<?php

use wcf\system\cronjob\CronjobScheduler;
use wcf\system\WCF;

return static function () {
    WCF::getTPL()->assign(
        'executeCronjobs',
        CronjobScheduler::getInstance()->getNextExec() < TIME_NOW && \defined('OFFLINE') && !OFFLINE
    );
};
