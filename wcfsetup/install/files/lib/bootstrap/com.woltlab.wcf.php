<?php

use wcf\system\acp\dashboard\event\AcpDashboardCollecting;
use wcf\system\cronjob\CronjobScheduler;
use wcf\system\event\EventHandler;
use wcf\system\event\listener\PackageUpdateListChangedLicenseListener;
use wcf\system\event\listener\PhraseChangedPreloadListener;
use wcf\system\event\listener\PipSyncedPhrasePreloadListener;
use wcf\system\event\listener\PreloadPhrasesCollectingListener;
use wcf\system\event\listener\UserLoginCancelLostPasswordListener;
use wcf\system\event\listener\UsernameValidatingCheckCharactersListener;
use wcf\system\language\event\LanguageImported;
use wcf\system\language\event\PhraseChanged;
use wcf\system\language\LanguageFactory;
use wcf\system\language\preload\command\ResetPreloadCache;
use wcf\system\language\preload\event\PreloadPhrasesCollecting;
use wcf\system\language\preload\PhrasePreloader;
use wcf\system\package\event\PackageInstallationPluginSynced;
use wcf\system\package\event\PackageListChanged;
use wcf\system\package\event\PackageUpdateListChanged;
use wcf\system\package\license\LicenseApi;
use wcf\system\session\event\PreserveVariablesCollecting;
use wcf\system\user\authentication\event\UserLoggedIn;
use wcf\system\user\authentication\LoginRedirect;
use wcf\system\user\event\UsernameValidating;
use wcf\system\WCF;
use wcf\system\worker\event\RebuildWorkerCollecting;

return static function (): void {
    $eventHandler = EventHandler::getInstance();

    WCF::getTPL()->assign(
        'executeCronjobs',
        CronjobScheduler::getInstance()->getNextExec() < TIME_NOW && \defined('OFFLINE') && !OFFLINE
    );

    $eventHandler->register(UserLoggedIn::class, UserLoginCancelLostPasswordListener::class);
    $eventHandler->register(PreserveVariablesCollecting::class, static function (PreserveVariablesCollecting $event) {
        $event->keys[] = LoginRedirect::SESSION_VAR_KEY;
    });

    $eventHandler->register(UsernameValidating::class, UsernameValidatingCheckCharactersListener::class);

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

    $eventHandler->register(PackageUpdateListChanged::class, PackageUpdateListChangedLicenseListener::class);

    $eventHandler->register(AcpDashboardCollecting::class, static function (AcpDashboardCollecting $event) {
        $event->register(new \wcf\system\acp\dashboard\box\NewsAcpDashboardBox());
        $event->register(new \wcf\system\acp\dashboard\box\UsersAwaitingApprovalAcpDashboardBox());
        $event->register(new \wcf\system\acp\dashboard\box\SystemInfoAcpDashboardBox());
        $event->register(new \wcf\system\acp\dashboard\box\CreditsAcpDashboardBox());
    });

    try {
        $licenseApi = new LicenseApi();
        $licenseData = $licenseApi->readFromFile();
        if ($licenseData !== null) {
            $brandingFree = $licenseData->woltlab['com.woltlab.brandingFree'] ?? '0.0';
            $expiresAt = $licenseData->license['expiryDates']['com.woltlab.brandingFree'] ?? \TIME_NOW;
            if ($brandingFree !== '0.0' && ($expiresAt === -1 || $expiresAt >= \TIME_NOW)) {
                define('WOLTLAB_BRANDING', false);
            }

            // Expire the cached license data after 60 days.
            if ($licenseData->creationDate->getTimestamp() < \TIME_NOW - 86400 * 60) {
                $licenseApi->clearLicenseFile();
            }
        }
    } catch (\Throwable) {
        // Reading the license file must never cause any errors.
    }

    if (!defined('WOLTLAB_BRANDING')) {
        define('WOLTLAB_BRANDING', true);
    }
};
