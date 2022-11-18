<?php

use wcf\system\cronjob\CronjobScheduler;
use wcf\system\event\EventHandler;
use wcf\system\event\listener\PhraseChangedPreloadListener;
use wcf\system\event\listener\PipSyncedPhrasePreloadListener;
use wcf\system\event\listener\PreloadPhrasesCollectingListener;
use wcf\system\event\listener\UserLoginCancelLostPasswordListener;
use wcf\system\language\event\LanguageImported;
use wcf\system\language\event\PhraseChanged;
use wcf\system\language\LanguageFactory;
use wcf\system\language\preload\command\ResetPreloadCache;
use wcf\system\language\preload\event\PreloadPhrasesCollecting;
use wcf\system\language\preload\PhrasePreloader;
use wcf\system\package\event\PackageInstallationPluginSynced;
use wcf\system\package\event\PackageListChanged;
use wcf\system\user\authentication\event\UserLoggedIn;
use wcf\system\WCF;

return static function (): void {
    $eventHandler = EventHandler::getInstance();

    WCF::getTPL()->assign(
        'executeCronjobs',
        CronjobScheduler::getInstance()->getNextExec() < TIME_NOW && \defined('OFFLINE') && !OFFLINE
    );

    $eventHandler->register(UserLoggedIn::class, UserLoginCancelLostPasswordListener::class);

    $eventHandler->register(PackageListChanged::class, static function () {
        foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
            $command = new ResetPreloadCache($language);
            $command();
        }
    });
    $eventHandler->register(LanguageImported::class, static function (LanguageImported $event) {
        $command = new ResetPreloadCache($event->language);
        $command();
    });
    $eventHandler->register(PhraseChanged::class, PhraseChangedPreloadListener::class);
    $eventHandler->register(PackageInstallationPluginSynced::class, PipSyncedPhrasePreloadListener::class);
    WCF::getTPL()->assign('phrasePreloader', new PhrasePreloader());
    $eventHandler->register(PreloadPhrasesCollecting::class, PreloadPhrasesCollectingListener::class);
};
