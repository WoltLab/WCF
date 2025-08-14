<?php

namespace wcf\data\user\notification;

use wcf\action\NotificationConfirmAction;
use wcf\command\user\notification\CreateStackableUserNotification;
use wcf\command\user\notification\CreateUserNotification;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\user\User;
use wcf\data\user\UserProfile;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\request\LinkHandler;
use wcf\system\style\FontAwesomeIcon;
use wcf\system\user\notification\event\IUserNotificationEvent;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * Executes user notification-related actions.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectAction<UserNotification, UserNotificationEditor>
 */
class UserNotificationAction extends AbstractDatabaseObjectAction
{
    /**
     * notification editor object
     * @var UserNotificationEditor
     */
    public $notificationEditor;

    /**
     * Creates a simple notification without stacking support, applies to legacy notifications too.
     *
     * @return  mixed[][]
     * @deprecated 6.3 use the `CreateDefaultUserNotification` command instead
     */
    public function createDefault()
    {
        return (new CreateUserNotification(
            $this->parameters['data']['eventID'],
            $this->parameters['data']['eventHash'],
            $this->getUserProfile($this->parameters['authorID']),
            $this->parameters['data']['objectID'],
            $this->parameters['data']['packageID'],
            $this->parameters['data']['baseObjectID'],
            $this->parameters['recipients'],
            $this->parameters['data']['additionalData']
        ))();
    }

    /**
     * Creates a notification or adds another author to an existing one.
     *
     * @return  mixed[][]
     *
     * @deprecated 6.3 use the `CreateStackableUserNotification` command instead.
     */
    public function createStackable()
    {
        return (new CreateStackableUserNotification(
            $this->parameters['data']['eventID'],
            $this->parameters['data']['eventHash'],
            $this->getUserProfile($this->parameters['authorID']),
            $this->parameters['data']['objectID'],
            $this->parameters['data']['packageID'],
            $this->parameters['data']['baseObjectID'],
            $this->parameters['recipients'],
            $this->parameters['data']['additionalData']
        ))();
    }

    private function getUserProfile(?int $authorID): UserProfile
    {
        if ($authorID === null) {
            return new UserProfile(new User(null, []));
        }
        if ($authorID === WCF::getUser()->userID) {
            return new UserProfile(WCF::getUser());
        }

        return UserProfileRuntimeCache::getInstance()->getObject($authorID);
    }

    /**
     * @since 5.5
     */
    public function validateGetNotificationData(): void
    {
    }

    /**
     * @return array{
     *  items: list<array{
     *      content: string,
     *      image: string,
     *      isUnread: bool,
     *      link: string,
     *      objectId: int,
     *      time: int,
     *      usernames: string[],
     *  }>,
     *  totalCount: int,
     * }
     * @since 5.5
     */
    public function getNotificationData(): array
    {
        $data = UserNotificationHandler::getInstance()->getMixedNotifications();

        $notifications = [];
        foreach ($data['notifications'] as $notificationData) {
            $notificationID = $notificationData['notificationID'];

            /** @var IUserNotificationEvent $event */
            $event = $notificationData['event'];

            if ($notificationData['authors'] === 1) {
                $image = $event->getAuthor()->getAvatar()->getImageTag(48);
            } else {
                $image = FontAwesomeIcon::fromValues('users')->toHtml(48);
            }

            if ($event->isConfirmed()) {
                $link = $event->getLink();
            } else {
                $link = LinkHandler::getInstance()->getControllerLink(
                    NotificationConfirmAction::class,
                    ['id' => $notificationID]
                );
            }

            $usernames = \array_map(static function (UserProfile $userProfile) {
                return $userProfile->getFormattedUsername();
            }, $event->getAuthors());

            $notifications[] = [
                'content' => $event->getMessage(),
                'image' => $image,
                'isUnread' => !$event->isConfirmed(),
                'link' => $link,
                'objectId' => $notificationID,
                'time' => $notificationData['time'],
                'usernames' => $usernames,
            ];
        }

        return [
            'items' => $notifications,
            'totalCount' => $data['notificationCount'],
        ];
    }

    /**
     * Validates parameters to mark a notification as confirmed.
     *
     * @return void
     */
    public function validateMarkAsConfirmed()
    {
        $this->notificationEditor = $this->getSingleObject();
        if ($this->notificationEditor->userID != WCF::getUser()->userID) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * Marks a notification as confirmed.
     *
     * @return array{markAsRead: int, totalCount: int}
     */
    public function markAsConfirmed()
    {
        UserNotificationHandler::getInstance()->markAsConfirmedByIDs([$this->notificationEditor->notificationID]);

        return [
            'markAsRead' => $this->notificationEditor->notificationID,
            'totalCount' => UserNotificationHandler::getInstance()->getNotificationCount(true),
        ];
    }

    /**
     * Validates parameters to mark all notifications of current user as confirmed.
     *
     * @return void
     */
    public function validateMarkAllAsConfirmed()
    {
        // does nothing
    }

    /**
     * Marks all notifications of current user as confirmed.
     *
     * @return array{markAllAsRead: bool}
     */
    public function markAllAsConfirmed()
    {
        // Step 1) Find the IDs of the unread notifications.
        // This is done in a separate step, because this allows the UPDATE query to
        // leverage fine-grained locking of exact rows based off the PRIMARY KEY.
        // Simply updating all notifications belonging to a specific user will need
        // to prevent concurrent threads from inserting new notifications for proper
        // consistency, possibly leading to deadlocks.
        $sql = "SELECT  notificationID
                FROM    wcf1_user_notification
                WHERE   userID = ?
                    AND confirmTime = ?
                    AND time < ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            WCF::getUser()->userID,
            0,
            TIME_NOW,
        ]);
        $notificationIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);

        if (!empty($notificationIDs)) {
            // Step 2) Mark the notifications as read.
            $condition = new PreparedStatementConditionBuilder();
            $condition->add('notificationID IN (?)', [$notificationIDs]);

            $sql = "UPDATE  wcf1_user_notification
                    SET     confirmTime = ?
                    {$condition}";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute(\array_merge([TIME_NOW], $condition->getParameters()));
        }

        // Step 4) Clear cached values.
        UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'userNotificationCount');

        return [
            'markAllAsRead' => true,
        ];
    }
}
