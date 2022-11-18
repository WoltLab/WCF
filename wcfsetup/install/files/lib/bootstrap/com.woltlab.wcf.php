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
use wcf\system\worker\event\RebuildWorkerCollecting;

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

    $eventHandler->register(RebuildWorkerCollecting::class, static function (RebuildWorkerCollecting $event) {
        $event->register(\wcf\system\worker\LikeRebuildDataWorker::class, -100);
        $event->register(\wcf\system\worker\ArticleRebuildDataWorker::class, 50);
        $event->register(\wcf\system\worker\PageRebuildDataWorker::class, 50);
        $event->register(\wcf\system\worker\PollRebuildDataWorker::class, 60);
        $event->register(\wcf\system\worker\UserActivityPointUpdateEventsWorker::class, 65);
        $event->register(\wcf\system\worker\UserRebuildDataWorker::class, 70);
        $event->register(\wcf\system\worker\UserActivityPointItemsRebuildDataWorker::class, 75);
        $event->register(\wcf\system\worker\CommentRebuildDataWorker::class, 120);
        $event->register(\wcf\system\worker\CommentResponseRebuildDataWorker::class, 121);
        $event->register(\wcf\system\worker\AttachmentRebuildDataWorker::class, 450);
        $event->register(\wcf\system\worker\MediaRebuildDataWorker::class, 450);
        $event->register(\wcf\system\worker\SitemapRebuildWorker::class, 500);
        $event->register(\wcf\system\worker\StatDailyRebuildDataWorker::class, 800);
    });
};
