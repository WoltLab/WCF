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
    WCF::getTPL()->assign(
        'executeCronjobs',
        CronjobScheduler::getInstance()->getNextExec() < TIME_NOW && \defined('OFFLINE') && !OFFLINE
    );

    EventHandler::getInstance()->register(UserLoggedIn::class, UserLoginCancelLostPasswordListener::class);

    EventHandler::getInstance()->register(PackageListChanged::class, static function () {
        foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
            $command = new ResetPreloadCache($language);
            $command();
        }
    });
    EventHandler::getInstance()->register(LanguageImported::class, static function (LanguageImported $event) {
        $command = new ResetPreloadCache($event->language);
        $command();
    });
    EventHandler::getInstance()->register(PhraseChanged::class, PhraseChangedPreloadListener::class);
    EventHandler::getInstance()->register(PackageInstallationPluginSynced::class, PipSyncedPhrasePreloadListener::class);
    WCF::getTPL()->assign('phrasePreloader', new PhrasePreloader());
    EventHandler::getInstance()->register(PreloadPhrasesCollecting::class, PreloadPhrasesCollectingListener::class);
};
