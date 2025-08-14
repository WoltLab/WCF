<?php

namespace wcf\command\user\notification;

use wcf\event\user\notification\AllUserNotificationsMarkAsRead;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\event\EventHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * Marks all user notifications as read.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class MarkAllUserNotificationsAsRead
{
    public function __construct(
        private readonly int $userID,
    ) {}

    public function __invoke(): void
    {
        // Step 1) Find the IDs of the unread notifications.
        // This is done in a separate step, because this allows the UPDATE query to
        // leverage fine-grained locking of exact rows based off the PRIMARY KEY.
        // Simply updating all notifications belonging to a specific user will need
        // to prevent concurrent threads from inserting new notifications for proper
        // consistency, possibly leading to deadlocks.
        $notificationIDs = $this->getUnreadNotificationIDs();

        if ($notificationIDs !== []) {
            // Step 2) Mark the notifications as read.
            $this->markNotificationsAsRead($notificationIDs);
        }

        $this->clearCache();

        $event = new AllUserNotificationsMarkAsRead($this->userID);
        EventHandler::getInstance()->fire($event);
    }

    /**
     * @return list<int>
     */
    private function getUnreadNotificationIDs(): array
    {
        $sql = "SELECT  notificationID
                FROM    wcf1_user_notification
                WHERE   userID = ?
                    AND confirmTime = ?
                    AND time < ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $this->userID,
            0,
            TIME_NOW,
        ]);

        return $statement->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * @param list<int> $notificationIDs
     */
    private function markNotificationsAsRead(array $notificationIDs): void
    {
        $condition = new PreparedStatementConditionBuilder();
        $condition->add('notificationID IN (?)', [$notificationIDs]);

        $sql = "UPDATE  wcf1_user_notification
                SET     confirmTime = ?
                {$condition}";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute(\array_merge([TIME_NOW], $condition->getParameters()));
    }

    private function clearCache(): void
    {
        UserStorageHandler::getInstance()->reset([$this->userID], 'userNotificationCount');
    }
}
