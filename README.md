IMEdge\\systemd
===============

systemd-related library. Currently: just a WatchDog notifier. WatchDog fires
automatically if required from the environment. You're responsible for calling
`setReady()` - and you might want to set Status and Reloading or error state.

Watchdog notifications will fire automagically based on whether `systemd` is
running your daemon or not. In case we cannot detect systemd through our
ENVironment, nothing happens - and the `ifRequired()` factory method will return
`false`.

Usage
-----

```php
<?php

use IMEdge\systemd\systemd;
use Revolt\EventLoop;

systemd::notificationSocket()?->setReady('My process is ready');
EventLoop::delay(10, function () {
    systemd::notificationSocket()?->setReady('Process status changed');
})
```

Changes
-------

### v1.0.0

* Changed namespace
* Refactored completely, it's now PHP 8.1+ only and works with RevoltPHP (AMPHP and ReactPHP)

### v0.4.0
* added a missing property to fix deprecation notices with PHP 8.2

### v0.3.0
* Notification socket has been moved to a dedicated class

### v0.2.0

* Add `has/getInvocationId()
* Provide Getters for SocketPath and WatchdogInterval
* Immediately fire the first Watchdog notification
* Log eventual issues when sending Watchdog notifications
* Allow to extend Watchdog timeout (v236+)

### v0.1.0

* First release
