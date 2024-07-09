<?php

namespace IMEdge\systemd;

use Exception;
use Revolt\EventLoop;
use RuntimeException;
use Socket;

use function posix_getpid;

class NotificationSocket
{
    protected Socket $socket;

    /**
     * The path to the systemd notification socket
     *
     * Usually /run/systemd/notify or similar
     */
    public readonly string $path;
    protected bool $ready = false;
    protected bool $failed = false;
    protected ?string $timerId = null;
    protected ?string $status = null;

    public function __construct(string $path)
    {
        if (systemd::instantiatedNotificationSocket()) {
            throw new RuntimeException('Cannot instantiate systemd notification socket twice');
        }

        if (@file_exists($path) && is_writable($path)) {
            $this->path = $path;
        } else {
            throw new RuntimeException("Unix Socket '$path' is not writable");
        }

        $this->start();
    }

    /**
     * Extend the Watchdog timeout
     *
     * Useful to inform systemd before slow startup/shutdown operations. This
     * is available since systemd v236. Older versions silently ignore this.
     */
    public function extendTimeout(int|float $seconds): void
    {
        $this->send([Parameter::EXTEND_TIMEOUT_USEC => (int) ($seconds * systemd::MICROSECONDS)]);
    }

    /**
     * Send a notification to the systemd watchdog
     */
    public function pingWatchDog(): void
    {
        $this->send([Parameter::WATCHDOG => true]);
    }

    public function setReady(?string $status = null): void
    {
        $this->ready = true;
        $params = [
            Parameter::READY   => true,
            Parameter::MAINPID => posix_getpid(),
        ];

        if ($status !== null) {
            $params[Parameter::STATUS] = $status;
            $this->status = $status;
        }

        $this->send($params);
    }

    /**
     * Set the (visible) service status
     */
    public function setStatus(?string $status): void
    {
        if ($status !== $this->status) {
            $this->status = $status;
            $this->send([Parameter::STATUS => $status]);
        }
    }

    public function setReloading(?string $status = null): void
    {
        $this->ready = false;
        $this->status = $status;
        $params = [Parameter::RELOADING => true];
        if ($status !== null) {
            $params[Parameter::STATUS] = $status;
        }
        $this->send($params);
    }

    public function setError(Exception|int $error, ?string $status = null): void
    {
        $this->ready = false;
        $this->status = $status;
        if ($error instanceof Exception) {
            $errNo = $error->getCode();
            $status = $status ?: $error->getMessage();
        } else {
            $errNo = $error;
        }
        $params = [];
        if ($status !== null) {
            $params[Parameter::STATUS] = $status;
            $this->status = $status;
        }

        $params[Parameter::ERRNO] = (string) $errNo;
        $this->send($params);
        $this->failed = true;
    }

    /**
     * Send custom parameters to systemd
     *
     * This is for internal use only, but might be used to test new functionality
     *
     * @param array<string,string|int|bool|null> $params
     * @internal
     */
    public function send(array $params): void
    {
        if ($this->failed) {
            throw new RuntimeException('Cannot notify systemd after failing');
        }

        $this->sendMessage(new NotificationMessage($params));
    }

    /**
     * Send custom parameters to systemd
     */
    protected function sendMessage(NotificationMessage $message): void
    {
        $messageString = (string) $message;
        $length = strlen($messageString);
        $result = @socket_send($this->socket, $messageString, $length, 0);
        if ($result === false) {
            $error = socket_last_error($this->socket);

            throw new RuntimeException(
                "Failed to send to SystemD: " . socket_strerror($error),
                $error
            );
        }
        if ($result !== $length) {
            throw new RuntimeException(
                "Wanted to send $length Bytes to SystemD, only $result have been sent"
            );
        }
    }

    protected function triggerWatchDog(): void
    {
        try {
            $this->pingWatchDog();
        } catch (Exception $e) {
            printf(
                "<%d>Failed to ping systemd watchdog: %s\n",
                LOG_ERR,
                $e->getMessage()
            );
        }
    }

    /**
     * Connect to the discovered socket
     *
     * No async logic, as this shouldn't block. If systemd blocks we're dead
     * anyway, so who cares
     */
    protected function connect(): void
    {
        $path = $this->path;
        $socket = @socket_create(AF_UNIX, SOCK_DGRAM, 0);
        if ($socket === false) {
            throw new RuntimeException('Unable to create socket');
        }

        if (! @socket_connect($socket, $path)) {
            $error = socket_last_error($socket);

            throw new RuntimeException(
                "Unable to connect to unix domain socket $path: " . socket_strerror($error),
                $error
            );
        }

        $this->socket = $socket;
    }

    protected function start(): void
    {
        $this->connect();
        EventLoop::queue($this->triggerWatchDog(...));
        $this->timerId = EventLoop::repeat(systemd::getWatchdogSeconds() / 2, $this->triggerWatchDog(...));
    }

    /**
     * Stop sending watchdog notifications
     *
     * Usually there is no need to do so, and the destructor does this anyway
     */
    protected function stop(): void
    {
        if ($this->timerId !== null) {
            EventLoop::cancel($this->timerId);
        }
    }

    public function __destruct()
    {
        @socket_close($this->socket);
        $this->stop();
    }
}
