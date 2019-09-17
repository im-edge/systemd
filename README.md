gipfl\\SystemD
==========================

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

use gipfl\SystemD\NotifySystemD;
use React\EventLoop\Factory as Loop;

$loop = Loop::create();
if ($notifier = NotifySystemD::ifRequired($loop)) {
    $notifier->setReady('My process is ready');
    $loop->addTimer(10, function () use ($notifier) {
        $notifier->setStatus('Process status changed');
    });
}

$loop->run();
```

Changes
-------

### v0.2.0

* Add `has/getInvocationId()
* Provide Getters for SocketPath and WatchdogInterval
* Immediately fire the first Watchdog notification
* Log eventual issues when sending Watchdog notifications
* Allow to extend Watchdog timeout (v236+)

### v0.2.0

* First release

Naming
------

Yes, we know that it reads `systemd` and not `SystemD` - sorry for hurting your
feelings. Unfortunately we released software depending on this library before we
realized that we committed this error. We'll fix it once we move this to ipl or
another namespace. Right now it isn't possible, as a transition from `systemd`
to `SystemD` would lead to problems in a world with `composer` and `PHP`, with
the latter being partially case insensitive.
