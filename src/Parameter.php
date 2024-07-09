<?php

namespace IMEdge\systemd;

/**
 * Comments/documentation are from sd_notify (3)
 */
class Parameter
{
    /**
     * STATUS=...
     *
     * Passes a single-line UTF-8 status string back to the service
     * manager that describes the service state. This is free-form
     * and can be used for various purposes: general state feedback,
     * fsck-like programs could pass completion percentages and
     * failing programs could pass a human-readable error message.
     * Example: "STATUS=Completed 66% of file system check..."
     *
     * Added in version 233.
     */
    public const STATUS = 'STATUS';

    /**
     * READY=1
     *
     * Tells the service manager that service startup is finished,
     * or the service finished re-loading its configuration. This is
     * only used by systemd if the service definition file has
     * Type=notify or Type=notify-reload set. Since there is little
     * value in signaling non-readiness, the only value services
     * should send is "READY=1" (i.e.  "READY=0" is not defined).
     */
    public const READY = 'READY';

    /**
     * MAINPID=...
     *
     * The main process ID (PID) of the service, in case the service
     * manager did not fork off the process itself. Example:
     * "MAINPID=4711".
     *
     * Added in version 233.
     */
    public const MAINPID = 'MAINPID';

    /**
     * ERRNO=...
     *
     * If a service fails, the errno-style error code, formatted as
     * string. Example: "ERRNO=2" for ENOENT.
     *
     * Added in version 233.
     */
    public const ERRNO = 'ERRNO';

    /**
     * RELOADING=1
     *
     * Tells the service manager that the service is beginning to
     * reload its configuration. This is useful to allow the service
     * manager to track the service's internal state, and present it
     * to the user. Note that a service that sends this notification
     * must also send a "READY=1" notification when it completed
     * reloading its configuration. Reloads the service manager is
     * notified about with this mechanisms are propagated in the
     * same way as they are when originally initiated through the
     * service manager. This message is particularly relevant for
     * Type=notify-reload services, to inform the service manager
     * that the request to reload the service has been received and
     * is now being processed.
     *
     * Added in version 217.
     */
    public const RELOADING = 'RELOADING';

    /**
     * STOPPING=1
     *
     * Tells the service manager that the service is beginning its
     * shutdown. This is useful to allow the service manager to
     * track the service's internal state, and present it to the
     * user.
     *
     * Added in version 217.
    */
    public const STOPPING = 'STOPPING';

    /**
     * EXTEND_TIMEOUT_USEC=...
     *
     * Tells the service manager to extend the startup, runtime or
     * shutdown service timeout corresponding the current state. The
     * value specified is a time in microseconds during which the
     * service must send a new message. A service timeout will occur
     * if the message isn't received, but only if the runtime of the
     * current state is beyond the original maximum times of
     * TimeoutStartSec=, RuntimeMaxSec=, and TimeoutStopSec=. See
     * systemd.service(5) for effects on the service timeouts.
     *
     * Added in version 236.
     */
    public const EXTEND_TIMEOUT_USEC = 'EXTEND_TIMEOUT_USEC';

    /**
     * WATCHDOG=1
     *
     * Tells the service manager to update the watchdog timestamp.
     * This is the keep-alive ping that services need to issue in
     * regular intervals if WatchdogSec= is enabled for it. See
     * systemd.service(5) for information how to enable this
     * functionality and sd_watchdog_enabled(3) for the details of
     * how the service can check whether the watchdog is enabled.
     *
     *
     * WATCHDOG=trigger
     *
     * Tells the service manager that the service detected an
     * internal error that should be handled by the configured
     * watchdog options. This will trigger the same behaviour as if
     * WatchdogSec= is enabled and the service did not send
     * "WATCHDOG=1" in time. Note that WatchdogSec= does not need to
     * be enabled for "WATCHDOG=trigger" to trigger the watchdog
     * action. See systemd.service(5) for information about the
     * watchdog behavior.
     *
     * Added in version 243.
     */
    public const WATCHDOG = 'WATCHDOG';

    /**
     * WATCHDOG_USEC=...
     *
     * Reset watchdog_usec value during runtime. Notice that this is
     * not available when using sd_event_set_watchdog() or
     * sd_watchdog_enabled(). Example : "WATCHDOG_USEC=20000000"
     *
     * Added in version 233.
     */
    public const WATCHDOG_USEC = 'WATCHDOG_USEC';

    /**
     *
     * MONOTONIC_USEC=...
     * A field carrying the monotonic timestamp (as per
     * CLOCK_MONOTONIC) formatted in decimal in μs, when the
     * notification message was generated by the client. This is
     * typically used in combination with "RELOADING=1", to allow
     * the service manager to properly synchronize reload cycles.
     * See systemd.service(5) for details, specifically
     * "Type=notify-reload".
     *
     * Added in version 253.
     */
    public const MONOTONIC_USEC = 'MONOTONIC_USEC';

    /**
     * NOTIFYACCESS=...
     * Reset the access to the service status notification socket
     * during runtime, overriding NotifyAccess= setting in the
     * service unit file. See systemd.service(5) for details,
     * specifically "NotifyAccess=" for a list of accepted values.
     *
     * Added in version 254.
     */
    public const NOTIFYACCESS = 'NOTIFYACCESS';

    /**
     * EXIT_STATUS=...
     *
     * The exit status of a service or the manager itself. Note that
     * systemd currently does not consume this value when sent by
     * services, so this assignment is only informational. The
     * manager will send this notification to its notification
     * socket, which may be used to to collect an exit status from
     * the system (a container or VM) as it shuts down. For example,
     * mkosi(1) makes use of this. The value to return may be set
     * via the systemctl(1) exit verb.
     *
     * Added in version 254.
     */
    public const EXIT_STATUS = 'EXIT_STATUS';

    // We ignore BUSERROR and FD* for now, out of scope

    public const TRUE = '1';
    public const FALSE = '2';
}
