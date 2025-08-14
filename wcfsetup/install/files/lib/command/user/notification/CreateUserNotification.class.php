<?php

namespace wcf\command\user\notification;

use wcf\data\user\notification\UserNotification;
use wcf\data\user\notification\UserNotificationEditor;
use wcf\data\user\User;
use wcf\data\user\UserProfile;
use wcf\system\WCF;

/**
 * Creates simple user notifications without stacking support.
 *
 * @author  Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   6.3
 */
final class CreateUserNotification
{
    public function __construct(
        private readonly int $eventID,
        private readonly string $eventHash,
        private readonly UserProfile $author,
        private readonly int $objectID,
        private readonly int $packageID,
        private readonly int $baseObjectID,
        /** @var array<int, User> */
        private readonly array $recipients,
        private readonly string $additionalData,
    ) {}

    /**
     * @return array<int, array{isNew: bool, object: UserNotification}>
     */
    public function __invoke(): array
    {
        $notifications = [];

        foreach ($this->recipients as $recipient) {
            $notification = $this->createNotificationForRecipient(
                $recipient,
                $this->packageID,
                $this->eventID,
                $this->objectID,
                $this->baseObjectID,
                $this->eventHash,
                $this->author->userID,
                $this->additionalData
            );

            $notifications[$recipient->userID] = [
                'isNew' => true,
                'object' => $notification,
            ];
        }

        $this->insertAuthors($notifications, $this->author->userID);

        return $notifications;
    }

    private function createNotificationForRecipient(
        User $recipient,
        int $packageID,
        int $eventID,
        int $objectID,
        int $baseObjectID,
        string $eventHash,
        int $authorID,
        string $additionalData
    ): UserNotification {
        return UserNotificationEditor::create([
            'packageID' => $packageID,
            'eventID' => $eventID,
            'objectID' => $objectID,
            'baseObjectID' => $baseObjectID,
            'eventHash' => $eventHash,
            'authorID' => $authorID,
            'mailNotified' => $this->shouldNotifyByMail($recipient) ? 0 : 1,
            'time' => \TIME_NOW,
            'timesTriggered' => 1,
            'additionalData' => $additionalData,
            'userID' => $recipient->userID,
        ]);
    }

    private function shouldNotifyByMail(User $recipient): bool
    {
        return $recipient->mailNotificationType === UserNotification::MAIL_NOTIFICATION_TYPE_NONE
            || $recipient->mailNotificationType === UserNotification::MAIL_NOTIFICATION_TYPE_INSTANT;
    }

    /**
     * @param array<int, array{isNew: bool, object: UserNotification}> $notifications
     */
    private function insertAuthors(array $notifications, int $authorID): void
    {
        if ($notifications === []) {
            return;
        }

        $sql = "INSERT INTO wcf1_user_notification_author
                            (notificationID, authorID, time)
                VALUES      (?, ?, ?)";
        $statement = WCF::getDB()->prepare($sql);

        WCF::getDB()->beginTransaction();
        foreach ($notifications as $notificationData) {
            $statement->execute([
                $notificationData['object']->notificationID,
                $authorID,
                \TIME_NOW,
            ]);
        }
        WCF::getDB()->commitTransaction();
    }
}
