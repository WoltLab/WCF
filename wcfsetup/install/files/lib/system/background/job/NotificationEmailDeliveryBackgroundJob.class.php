<?php

namespace wcf\system\background\job;

use wcf\data\email\log\entry\EmailLogEntry;
use wcf\data\user\notification\UserNotification;
use wcf\data\user\User;
use wcf\system\session\SessionHandler;
use wcf\system\user\notification\event\IUserNotificationEvent;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\WCF;

/**
 * A NotificationEmailDelivery wraps EmailDeliverys and skips sending if the given associated
 * notification is no longer valid.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.3
 */
class NotificationEmailDeliveryBackgroundJob extends AbstractBackgroundJob
{
    const MAX_FAILURES = EmailDeliveryBackgroundJob::MAX_FAILURES;

    /**
     * @var EmailDeliveryBackgroundJob
     */
    private $job;

    /**
     * @var int
     */
    private $notificationID;

    /**
     * Wraps the given EmailDeliveryBackgroundJob and associates it with the given notification.
     *
     * The recipient is technically redundant, because the information can be retrieved using $notification->userID,
     * the value is used as a safety check within the constructor to make sure all the checks run against the expected
     * user.
     *
     * @param EmailDeliveryBackgroundJob $job
     * @param User $recipient
     * @param UserNotification $notification
     */
    public function __construct(EmailDeliveryBackgroundJob $job, UserNotification $notification, User $recipient)
    {
        $this->job = $job;
        $this->notificationID = $notification->notificationID;

        if ($notification->userID != $recipient->userID) {
            throw new \InvalidArgumentException("Mismatching userIDs within notification (" . $notification->userID . ") and recipient (" . $recipient->userID . ").");
        }
    }

    /**
     * Pass the failure along to the inner job to benefit from the retryAfter() logic.
     */
    public function onFailure()
    {
        $this->job->fail();
    }

    /**
     * Inherit the retryAfter logic of the inner job.
     */
    public function retryAfter()
    {
        return $this->job->retryAfter();
    }

    /**
     * @inheritDoc
     */
    public function perform()
    {
        // see UserNotificationHandler::fetchNotifications()
        $sql = "SELECT      notification.*, notification_event.eventID, object_type.objectType
                FROM        wcf1_user_notification notification
                LEFT JOIN   wcf1_user_notification_event notification_event
                ON          notification_event.eventID = notification.eventID
                LEFT JOIN   wcf1_object_type object_type
                ON          object_type.objectTypeID = notification_event.objectTypeID
                WHERE       notification.notificationID = ?
                ORDER BY    notification.time DESC";
        $statement = WCF::getDB()->prepare($sql, 1);
        $statement->execute([$this->notificationID]);

        /** @var UserNotification $notification */
        $notification = $statement->fetchObject(UserNotification::class);
        $statement->closeCursor();

        // Drop email if the notification is deleted.
        if (!$notification || !$notification->notificationID) {
            $this->job->updateStatus(
                EmailLogEntry::STATUS_DISCARDED,
                'notification does not exist'
            );

            return;
        }

        $user = WCF::getUser();
        try {
            // Switch user, because processNotifications() checks the permissions as the current user.
            SessionHandler::getInstance()->changeUser(new User($notification->userID), true);

            if (!WCF::getUser()->isEmailConfirmed()) {
                $this->job->updateStatus(
                    EmailLogEntry::STATUS_DISCARDED,
                    'email is no longer confirmed'
                );

                return;
            }

            if (WCF::getUser()->banned) {
                $this->job->updateStatus(
                    EmailLogEntry::STATUS_DISCARDED,
                    'user is banned'
                );

                return;
            }

            $processedNotifications = UserNotificationHandler::getInstance()->processNotifications([$notification]);

            // Drop email if the processing dropped the notification (most likely due to a lack of permissions).
            if ($processedNotifications['count'] == 0) {
                $this->job->updateStatus(
                    EmailLogEntry::STATUS_DISCARDED,
                    'lack of permissions to see notification'
                );

                return;
            }

            // If no notification was dropped we expect to get back exactly one notification ...
            if ($processedNotifications['count'] != 1) {
                throw new \LogicException("Unreachable");
            }

            $processedNotification = $processedNotifications['notifications'][0];

            // ... and we expect that this one notification is the one we passed in.
            if ($processedNotification['notificationID'] != $notification->notificationID) {
                throw new \LogicException("Unreachable");
            }

            /** @var IUserNotificationEvent $event */
            $event = $processedNotification['event'];

            // Drop email if the notification is already confirmed.
            if ($event->isConfirmed()) {
                $this->job->updateStatus(
                    EmailLogEntry::STATUS_DISCARDED,
                    'obsolete, notification is already confirmed'
                );

                return;
            }

            $this->job->perform();
        } finally {
            SessionHandler::getInstance()->changeUser($user, true);
        }
    }
}
