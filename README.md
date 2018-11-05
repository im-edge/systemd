gipfl\\SystemD
==========================

SystemD-related library. Currently: just a WatchDog notifier. WatchDog fires
automatically if required from the environment. You're responsible for calling
`setReady()` - and you might want to set Status and Reloading or error state.

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
