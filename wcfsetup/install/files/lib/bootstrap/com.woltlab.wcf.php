<?php

use wcf\system\cronjob\CronjobScheduler;
use wcf\system\event\EventHandler;
use wcf\system\event\listener\UserLoginCancelLostPasswordListener;
use wcf\system\user\authentication\event\UserLoggedIn;
use wcf\system\WCF;

return static function (): void {
    WCF::getTPL()->assign(
        'executeCronjobs',
        CronjobScheduler::getInstance()->getNextExec() < TIME_NOW && \defined('OFFLINE') && !OFFLINE
    );

    EventHandler::getInstance()->register(UserLoggedIn::class, UserLoginCancelLostPasswordListener::class);
};
