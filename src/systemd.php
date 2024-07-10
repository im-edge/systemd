<?php

namespace IMEdge\systemd;

use RuntimeException;

/**
 * @codingStandardsIgnoreStart Let's respect systemd naming convention :-)
 */
class systemd
{
    // @codingStandardsIgnoreEnd
    public const ENV_WATCHDOG_USEC = 'WATCHDOG_USEC';

    /**
     * $NOTIFY_SOCKET
     *
     * Set by the service manager for supervised processes for
     * status and start-up completion notification. This environment
     * variable specifies the socket sd_notify() talks to. See above
     * for details.
     */
    public const ENV_NOTIFY_SOCKET = 'NOTIFY_SOCKET';
    public const ENV_INVOCATION_ID = 'INVOCATION_ID';
    public const MICROSECONDS = 1_000_000;
    protected const DEFAULT_WATCHDOG_USEC = 5 * self::MICROSECONDS;

    protected static NotificationSocket|false|null $notificationSocket = null;
    protected static string|false|null $invocationId;
    /** @var array<string, string>|null */
    protected static ?array $env = null;
    protected static ?float $watchdogSeconds = null;

    public static function startedThisProcess(): bool
    {
        return self::hasEnv(self::ENV_NOTIFY_SOCKET);
    }

    public static function notificationSocket(): ?NotificationSocket
    {
        if (self::$notificationSocket === false) {
            return null;
        }

        $socketPath = self::getEnv(self::ENV_NOTIFY_SOCKET);
        if ($socketPath === null || ! str_starts_with($socketPath, '/')) {
            // We support only Unix sockets, no vsock (vsock://) and no abstract namespace socket (@)
            self::$notificationSocket = false;
            return null;
        }

        return self::$notificationSocket = new NotificationSocket($socketPath);
    }

    /**
     * @internal
     */
    public static function instantiatedNotificationSocket(): bool
    {
        return self::$notificationSocket instanceof NotificationSocket;
    }

    public static function getWatchdogSeconds(): float
    {
        return self::$watchdogSeconds
            ??= (float) self::getEnv(self::ENV_WATCHDOG_USEC, (string) self::DEFAULT_WATCHDOG_USEC)
                / self::MICROSECONDS;
    }

    /**
     * If INVOCATION_ID is available in the given ENV array: keep it
     *
     * Fails in case we do not get a 128bit string (UUID)
     */
    public static function getInvocationId(): ?string
    {
        if (self::$invocationId === false) {
            return null;
        }
        if ($id = self::getEnv(self::ENV_INVOCATION_ID)) {
            if (strlen($id) === 32) {
                self::$invocationId = $id;

                return $id;
            }
            throw new RuntimeException(sprintf('Unsupported %s="%s"', self::ENV_INVOCATION_ID, $id));
        }
        self::$invocationId = false;

        return null;
    }

    protected static function getEnv(string $key, ?string $default = null): ?string
    {
        self::$env ??= $_SERVER;
        return self::$env[$key] ?? $default;
    }

    protected static function hasEnv(string $key): bool
    {
        self::$env ??= $_SERVER;
        return array_key_exists($key, self::$env);
    }

    /**
     * For testing purposes only
     *
     * @param array<string, string> $env
     * @internal
     */
    public static function setEnv(array $env): void
    {
        self::$env = $env;
    }
}
