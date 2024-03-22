<?php

use wcf\system\cronjob\CronjobScheduler;
use wcf\system\event\EventHandler;
use wcf\system\file\processor\event\FileProcessorCollecting;
use wcf\system\language\LanguageFactory;
use wcf\system\language\preload\command\ResetPreloadCache;
use wcf\system\language\preload\PhrasePreloader;
use wcf\system\package\license\LicenseApi;
use wcf\system\user\authentication\LoginRedirect;
use wcf\system\WCF;

return static function (): void {
    $eventHandler = EventHandler::getInstance();

    WCF::getTPL()->assign(
        'executeCronjobs',
        CronjobScheduler::getInstance()->getNextExec() < TIME_NOW && \defined('OFFLINE') && !OFFLINE
    );

    $eventHandler->register(
        \wcf\event\user\authentication\UserLoggedIn::class,
        \wcf\system\event\listener\UserLoginCancelLostPasswordListener::class
    );
    $eventHandler->register(
        \wcf\event\session\PreserveVariablesCollecting::class,
        static function (\wcf\event\session\PreserveVariablesCollecting $event) {
            $event->keys[] = LoginRedirect::SESSION_VAR_KEY;
        }
    );

    $eventHandler->register(
        \wcf\event\user\UsernameValidating::class,
        \wcf\system\event\listener\UsernameValidatingCheckCharactersListener::class
    );

    $eventHandler->register(
        \wcf\event\user\RegistrationSpamChecking::class,
        \wcf\system\event\listener\RegistrationSpamCheckingSfsListener::class
    );
    $eventHandler->register(
        \wcf\event\page\ContactFormSpamChecking::class,
        \wcf\system\event\listener\ContactFormSpamCheckingSfsListener::class
    );
    $eventHandler->register(
        \wcf\event\message\MessageSpamChecking::class,
        \wcf\system\event\listener\MessageSpamCheckingSfsListener::class
    );

    $eventHandler->register(
        \wcf\event\package\PackageListChanged::class,
        static function () {
            foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
                $command = new ResetPreloadCache($language);
                $command();
            }
        }
    );
    $eventHandler->register(
        \wcf\event\language\LanguageImported::class,
        static function (\wcf\event\language\LanguageImported $event) {
            $command = new ResetPreloadCache($event->language);
            $command();
        }
    );
    $eventHandler->register(
        \wcf\event\language\PhraseChanged::class,
        \wcf\system\event\listener\PhraseChangedPreloadListener::class
    );
    $eventHandler->register(
        \wcf\event\package\PackageInstallationPluginSynced::class,
        \wcf\system\event\listener\PipSyncedPhrasePreloadListener::class
    );
    WCF::getTPL()->assign('phrasePreloader', new PhrasePreloader());
    $eventHandler->register(
        \wcf\event\language\PreloadPhrasesCollecting::class,
        \wcf\system\event\listener\PreloadPhrasesCollectingListener::class
    );

    $eventHandler->register(
        \wcf\event\worker\RebuildWorkerCollecting::class,
        static function (\wcf\event\worker\RebuildWorkerCollecting $event) {
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
        }
    );

    $eventHandler->register(
        \wcf\event\package\PackageUpdateListChanged::class,
        \wcf\system\event\listener\PackageUpdateListChangedLicenseListener::class
    );

    $eventHandler->register(
        \wcf\event\acp\dashboard\box\BoxCollecting::class,
        static function (\wcf\event\acp\dashboard\box\BoxCollecting $event) {
            $event->register(new \wcf\system\acp\dashboard\box\NewsAcpDashboardBox());
            $event->register(new \wcf\system\acp\dashboard\box\StatusMessageAcpDashboardBox());
            $event->register(new \wcf\system\acp\dashboard\box\ExpiringLicensesAcpDashboardBox());
            $event->register(new \wcf\system\acp\dashboard\box\UsersAwaitingApprovalAcpDashboardBox());
            $event->register(new \wcf\system\acp\dashboard\box\SystemInfoAcpDashboardBox());
            $event->register(new \wcf\system\acp\dashboard\box\CreditsAcpDashboardBox());
        }
    );

    $eventHandler->register(
        \wcf\event\endpoint\ControllerCollecting::class,
        static function (\wcf\event\endpoint\ControllerCollecting $event) {
            $event->register(new \wcf\system\endpoint\controller\core\files\PostUpload);
            $event->register(new \wcf\system\endpoint\controller\core\files\upload\PostChunk);
            $event->register(new \wcf\system\endpoint\controller\core\messages\GetMentionSuggestions);
            $event->register(new \wcf\system\endpoint\controller\core\sessions\DeleteSession);
        }
    );

    $eventHandler->register(FileProcessorCollecting::class, static function (FileProcessorCollecting $event) {
        $event->register(new \wcf\system\file\processor\AttachmentFileProcessor());
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
