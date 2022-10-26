<?php

namespace wcf\system\cronjob;

use wcf\data\cronjob\Cronjob;
use wcf\data\email\log\entry\EmailLogEntryAction;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\flood\FloodControl;
use wcf\system\user\multifactor\EmailMultifactorMethod;
use wcf\system\visitTracker\VisitTracker;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * Cronjob for a daily system cleanup.
 *
 * @author  Marcel Werk
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Cronjob
 */
class DailyCleanUpCronjob extends AbstractCronjob
{
    /**
     * @inheritDoc
     */
    public function execute(Cronjob $cronjob)
    {
        parent::execute($cronjob);

        // clean up search keywords
        $sql = "SELECT  AVG(searches) AS searches
                FROM    wcf1_search_keyword";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();
        if (($row = $statement->fetchArray()) !== false) {
            $sql = "DELETE FROM wcf1_search_keyword
                    WHERE       searches <= ?
                            AND lastSearchTime < ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([
                \floor($row['searches'] / 4),
                TIME_NOW - 86400 * 30,
            ]);
        }

        // clean up notifications
        $sql = "DELETE FROM wcf1_user_notification
                WHERE       time < ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            TIME_NOW - 86400 * USER_CLEANUP_NOTIFICATION_LIFETIME,
        ]);

        // clean up user activity events
        $sql = "DELETE FROM wcf1_user_activity_event
                WHERE       time < ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            TIME_NOW - 86400 * USER_CLEANUP_ACTIVITY_EVENT_LIFETIME,
        ]);

        // clean up profile visitors
        $sql = "DELETE FROM wcf1_user_profile_visitor
                WHERE       time < ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            TIME_NOW - 86400 * USER_CLEANUP_PROFILE_VISITOR_LIFETIME,
        ]);

        VisitTracker::getInstance()->prune();

        // clean up cronjob log
        $sql = "DELETE FROM wcf1_cronjob_log
                WHERE       execTime < ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            TIME_NOW - (86400 * 7),
        ]);

        // clean up session access log
        $sql = "DELETE FROM wcf1_acp_session_access_log
                WHERE       sessionLogID IN (
                                SELECT  sessionLogID
                                FROM    wcf1_acp_session_log
                                WHERE   lastActivityTime < ?
                            )";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            TIME_NOW - (86400 * 30),
        ]);

        // clean up session log
        $sql = "DELETE FROM wcf1_acp_session_log
                WHERE       lastActivityTime < ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            TIME_NOW - (86400 * 30),
        ]);

        // clean up search data
        $sql = "DELETE FROM wcf1_search
                WHERE       searchTime < ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            TIME_NOW - 86400,
        ]);

        // clean up expired edit history entries
        if (MODULE_EDIT_HISTORY) {
            if (EDIT_HISTORY_EXPIRATION) {
                $sql = "DELETE FROM wcf1_edit_history_entry
                        WHERE       obsoletedAt < ?";
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute([
                    TIME_NOW - 86400 * EDIT_HISTORY_EXPIRATION,
                ]);
            }
        } else {
            // edit history is disabled, prune old versions
            $sql = "DELETE FROM wcf1_edit_history_entry";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute();
        }

        // clean up user authentication failure log
        if (ENABLE_USER_AUTHENTICATION_FAILURE) {
            $sql = "DELETE FROM wcf1_user_authentication_failure
                    WHERE       time < ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([
                TIME_NOW - 86400 * USER_AUTHENTICATION_FAILURE_EXPIRATION,
            ]);
        }

        if (MODIFICATION_LOG_EXPIRATION > 0) {
            $sql = "DELETE FROM wcf1_modification_log
                    WHERE       time < ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([
                TIME_NOW - 86400 * MODIFICATION_LOG_EXPIRATION,
            ]);
        }

        // clean up error logs
        $files = @\glob(WCF_DIR . 'log/*.txt');
        if (\is_array($files)) {
            foreach ($files as $filename) {
                if (\filemtime($filename) < TIME_NOW - 86400 * 14) {
                    @\unlink($filename);
                }
            }
        }

        // clean up temporary folder
        $tempFolder = FileUtil::getTempFolder();
        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($tempFolder, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($it as $file) {
            if ($file->getPathname() === $tempFolder) {
                continue;
            }
            if ($file->getPathname() === $tempFolder . '/.htaccess') {
                continue;
            }

            if ($file->getMTime() < TIME_NOW - 86400) {
                if ($file->isDir()) {
                    @\rmdir($file->getPathname());
                } elseif ($file->isFile()) {
                    @\unlink($file->getPathname());
                }
            }
        }

        // clean up proxy images
        if (MODULE_IMAGE_PROXY && IMAGE_PROXY_ENABLE_PRUNE) {
            $it = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(WCF_DIR . 'images/proxy/', \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($it as $file) {
                if ($file->getPathname() === WCF_DIR . 'images/proxy/.htaccess') {
                    continue;
                }

                if ($file->isFile() && $file->getMTime() < (TIME_NOW - 86400 * IMAGE_PROXY_EXPIRATION)) {
                    @\unlink($file->getPathname());
                }
            }
        }

        if (BLACKLIST_SFS_ENABLE) {
            $timeLimit = TIME_NOW - 31 * 86400;

            $sql = "DELETE FROM wcf1_blacklist_entry
                    WHERE       lastSeen < ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([
                \gmdate('Y-m-d H:i:s', $timeLimit),
            ]);

            $sql = "DELETE FROM wcf1_blacklist_status
                    WHERE       date < ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([
                \gmdate('Y-m-d', $timeLimit),
            ]);
        }

        FloodControl::getInstance()->prune();
        EmailMultifactorMethod::prune();
        (new EmailLogEntryAction([], 'prune'))->executeAction();
    }
}
