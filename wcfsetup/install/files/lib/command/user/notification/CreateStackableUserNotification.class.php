<?php

namespace wcf\command\user\notification;

use wcf\data\user\notification\UserNotification;
use wcf\data\user\notification\UserNotificationEditor;
use wcf\data\user\notification\UserNotificationList;
use wcf\data\user\User;
use wcf\data\user\UserProfile;
use wcf\system\user\notification\event\IUserNotificationEvent;
use wcf\system\user\notification\object\IUserNotificationObject;
use wcf\system\user\notification\object\type\IUserNotificationObjectType;
use wcf\system\WCF;

/**
 * Creates stackable user notifications.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class CreateStackableUserNotification
{
    public function __construct(
        private readonly IUserNotificationEvent $event,
        private readonly UserProfile $author,
        private readonly IUserNotificationObject $object,
        private readonly IUserNotificationObjectType $objectType,
        private readonly int $baseObjectID,
        /** @var array<int, User> */
        private readonly array $recipients,
        /** @var array<string, mixed> */
        private readonly array $additionalData = [],
    ) {}

    /**
     * @return array<int, array{isNew: bool, object: UserNotification}>
     */
    public function __invoke(): array
    {
        $existingNotifications = $this->getExistingNotifications($this->event, $this->recipients);

        $notifications = [];
        foreach ($this->recipients as $recipient) {
            $notification = ($existingNotifications[$recipient->userID] ?? null);
            $isNew = ($notification === null);

            if ($notification === null) {
                $notification = $this->createNotification(
                    $this->objectType,
                    $this->event,
                    $this->object,
                    $this->baseObjectID,
                    $this->author,
                    \serialize($this->additionalData),
                    $recipient,
                );
            }

            $notifications[$recipient->userID] = [
                'isNew' => $isNew,
                'object' => $notification,
            ];
        }

        $this->sortNotificationsById($notifications);

        $notificationIDs = $this->createUserNotifications($this->author, $notifications);

        $updatedNotifications = $this->getUserNotificationsByIds($notificationIDs);

        return \array_map(static function ($notificationData) use ($updatedNotifications) {
            $notificationData['object'] = $updatedNotifications[$notificationData['object']->notificationID];

            return $notificationData;
        }, $notifications);
    }

    /**
     * @param array<int, array{isNew: bool, object: UserNotification}> $notifications
     *
     * @return list<int>
     */
    private function createUserNotifications(UserProfile $author, array $notifications): array
    {
        if ($notifications === []) {
            return [];
        }

        $sql = "INSERT IGNORE INTO  wcf1_user_notification_author
                                    (notificationID, authorID, time)
                VALUES              (?, ?, ?)";
        $authorStatement = WCF::getDB()->prepare($sql);
        $sql = "UPDATE  wcf1_user_notification
                SET     timesTriggered = timesTriggered + ?,
                        guestTimesTriggered = guestTimesTriggered + ?
                WHERE   notificationID = ?";
        $triggerStatement = WCF::getDB()->prepare($sql);

        $authorId = $author->userID;
        $isGuestTrigger = $authorId ? 1 : 0;
        $now = TIME_NOW;
        $notificationIDs = [];

        WCF::getDB()->beginTransaction();
        foreach ($notifications as $notificationData) {
            $notificationID = $notificationData['object']->notificationID;
            $notificationIDs[] = $notificationID;

            $authorStatement->execute([
                $notificationID,
                $authorId,
                $now,
            ]);

            $triggerStatement->execute([
                1,
                $isGuestTrigger,
                $notificationID,
            ]);
        }
        WCF::getDB()->commitTransaction();

        return $notificationIDs;
    }

    /**
     * @param list<int> $notificationIDs
     *
     * @return array<int, UserNotification>
     */
    private function getUserNotificationsByIds(array $notificationIDs): array
    {
        $notificationList = new UserNotificationList();
        $notificationList->setObjectIDs($notificationIDs);
        $notificationList->readObjects();

        return $notificationList->getObjects();
    }

    /**
     * @param array<int, User> $recipients
     *
     * @return array<int, UserNotification>
     */
    private function getExistingNotifications(IUserNotificationEvent $event, array $recipients): array
    {
        $notificationList = new UserNotificationList();
        $notificationList->getConditionBuilder()->add("eventID = ?", [$event->eventID]);
        $notificationList->getConditionBuilder()->add("eventHash = ?", [$event->getEventHash()]);
        $notificationList->getConditionBuilder()->add("userID IN (?)", [\array_keys($recipients)]);
        $notificationList->getConditionBuilder()->add("confirmTime = ?", [0]);
        $notificationList->readObjects();

        $existingNotifications = [];
        foreach ($notificationList->getObjects() as $notification) {
            $existingNotifications[$notification->userID] = $notification;
        }

        return $existingNotifications;
    }

    /**
     * @param array<int, array{isNew: bool, object: UserNotification}> $notifications
     */
    private function sortNotificationsById(array &$notifications): void
    {
        \uasort($notifications, [self::class, 'compareByNotificationId']);
    }

    /**
     * Comparator for user notifications by their notificationID.
     *
     * @param array{isNew: bool, object: UserNotification} $left
     * @param array{isNew: bool, object: UserNotification} $right
     */
    private static function compareByNotificationId(array $left, array $right): int
    {
        return $left['object']->notificationID <=> $right['object']->notificationID;
    }

    private function createNotification(
        IUserNotificationObjectType $objectType,
        IUserNotificationEvent $event,
        IUserNotificationObject $object,
        int $baseObjectID,
        ?UserProfile $author,
        string $additionalData,
        User $recipient,
    ): UserNotification {
        $mailNotified = (($recipient->mailNotificationType === 'none' || $recipient->mailNotificationType === 'instant') ? 1 : 0);

        return UserNotificationEditor::create([
            'packageID' => $objectType->packageID,
            'eventID' => $event->eventID,
            'objectID' => $object->getObjectID(),
            'baseObjectID' => $baseObjectID,
            'eventHash' => $event->getEventHash(),
            'authorID' => $author->userID ?: null,
            'mailNotified' => $mailNotified ? 0 : 1,
            'time' => TIME_NOW,
            'additionalData' => $additionalData,
            'userID' => $recipient->userID,
        ]);
    }
}
