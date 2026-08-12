<?php

use wcf\system\cache\source\DiskCacheSource;

// Unconditionally clear the disk cache regardless of the active cache method in
// order to forcefully remove any v1 or v2 cache artifacts.
$cache = new DiskCacheSource();
$cache->flushAll();
