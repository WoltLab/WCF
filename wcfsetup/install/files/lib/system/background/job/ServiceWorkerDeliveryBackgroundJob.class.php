<?php

namespace wcf\system\background\job;

use wcf\data\service\worker\ServiceWorker;
use wcf\data\service\worker\ServiceWorkerEditor;
use wcf\data\style\Style;
use wcf\data\user\notification\UserNotification;
use wcf\data\user\User;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\service\worker\ServiceWorkerHandler;
use wcf\system\session\SessionHandler;
use wcf\system\style\StyleHandler;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\WCF;
use wcf\util\JSON;
use wcf\util\StringUtil;

/**
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class ServiceWorkerDeliveryBackgroundJob extends AbstractUniqueBackgroundJob
{
    private const MAX_TIME = 10.0;

    #[\Override]
    public function perform()
    {
        $startTime = \microtime(true);
        do {
            $sql = "SELECT   workerID, notificationID
                    FROM     wcf1_service_worker_notification
                    ORDER BY time";
            $statement = WCF::getDB()->prepare($sql, 5);
            $statement->execute();

            $sql = "DELETE FROM wcf1_service_worker_notification
                    WHERE       workerID = ?
                            AND notificationID = ?";
            $deleteStatement = WCF::getDB()->prepare($sql);

            while ($row = $statement->fetchArray()) {
                $this->sendNotification($row['workerID'], $row['notificationID']);
                $deleteStatement->execute([
                    $row['workerID'],
                    $row['notificationID'],
                ]);
            }

            $timeElapsed = \microtime(true) - $startTime;
        } while ($this->queueAgain() && $timeElapsed < self::MAX_TIME);
    }

    private function sendNotification(int $serviceWorkerID, int $notificationID): void
    {
        $serviceWorker = new ServiceWorker($serviceWorkerID);
        $user = UserProfileRuntimeCache::getInstance()->getObject($serviceWorker->userID);
        if ($user === null) {
            // The user does not exist anymore.
            return;
        }

        $style = new Style($user->styleID);
        if (!$style->styleID) {
            $style = StyleHandler::getInstance()->getStyle();
        }

        /** @see NotificationEmailDeliveryBackgroundJob::perform() */
        $sql = "SELECT      notification.*, notification_event.eventID, object_type.objectType
                FROM        wcf1_user_notification notification
                LEFT JOIN   wcf1_user_notification_event notification_event
                ON          notification_event.eventID = notification.eventID
                LEFT JOIN   wcf1_object_type object_type
                ON          object_type.objectTypeID = notification_event.objectTypeID
                WHERE       notification.notificationID = ?
                ORDER BY    notification.time DESC";
        $statement = WCF::getDB()->prepare($sql, 1);
        $statement->execute([$notificationID]);

        /** @var UserNotification $notification */
        $notification = $statement->fetchObject(UserNotification::class);
        $statement->closeCursor();
        if (!$notification || !$notification->notificationID) {
            return;
        }
        $user = WCF::getUser();
        try {
            SessionHandler::getInstance()->changeUser(new User($notification->userID), true);
            $processedNotifications = UserNotificationHandler::getInstance()->processNotifications([$notification]);
            if ($processedNotifications['count'] == 0) {
                return;
            }
            \assert($processedNotifications['count'] === 1);
            $processedNotification = $processedNotifications['notifications'][0];
            \assert($processedNotification['notificationID'] === $notification->notificationID);
            $event = $processedNotification['event'];
            if ($event->isConfirmed()) {
                return;
            }

            $content = [
                "title" => $event->getTitle(),
                "message" => StringUtil::stripHTML($this->replaceImagesWithAltText($event->getMessage())),
                "url" => $event->getLink(),
                "notificationID" => $notification->notificationID,
                "time" => $notification->time,
                "icon" => $style->getFaviconAppleTouchIcon(),
            ];

            $report = ServiceWorkerHandler::getInstance()->sendOneNotification($serviceWorker, JSON::encode($content));
            if (!$report->isSuccess()) {
                (new ServiceWorkerEditor($serviceWorker))->delete();
            }
        } finally {
            SessionHandler::getInstance()->changeUser($user, true);
        }
    }

    #[\Override]
    public function queueAgain(): bool
    {
        $sql = "SELECT COUNT(*)
                FROM   wcf1_service_worker_notification";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();

        return $statement->fetchSingleColumn() > 0;
    }

    private function replaceImagesWithAltText(string $message): string
    {
        $document = new \DOMDocument('1.0', 'UTF-8');

        $useInternalErrors = \libxml_use_internal_errors(true);
        $document->loadHTML(
            '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head><body>' . $message . '</body></html>'
        );
        \libxml_clear_errors();
        \libxml_use_internal_errors($useInternalErrors);

        foreach ($document->getElementsByTagName('img') as $image) {
            \assert($image instanceof \DOMElement);
            $image->replaceWith($image->getAttribute('alt'));
        }

        return $document->textContent;
    }
}
