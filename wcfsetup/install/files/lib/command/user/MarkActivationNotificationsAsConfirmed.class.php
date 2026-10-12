<?php

namespace wcf\command\user;

use wcf\data\user\User;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\WCF;

/**
 * Marks the notifications about users awaiting activation as confirmed for
 * all recipients whose (possibly stacked) notification includes the given
 * user, as the administrator has taken care of it.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class MarkActivationNotificationsAsConfirmed
{
    private const EVENT_NAME = 'needActivation';
    private const OBJECT_TYPE = 'com.woltlab.wcf.user.registration.notification';

    public function __construct(
        private readonly User $user,
    ) {}

    public function __invoke(): void
    {
        $recipientIDs = $this->getRecipientIDs();
        if ($recipientIDs === []) {
            return;
        }

        UserNotificationHandler::getInstance()->markAsConfirmed(
            self::EVENT_NAME,
            self::OBJECT_TYPE,
            $recipientIDs
        );
    }

    /**
     * Returns the ids of the users with an unconfirmed notification that
     * lists this user as one of its authors. Notifications are stacked,
     * therefore the `objectID` only refers to the first registered user.
     *
     * @return int[]
     */
    private function getRecipientIDs(): array
    {
        $event = UserNotificationHandler::getInstance()->getEvent(self::OBJECT_TYPE, self::EVENT_NAME);
        if ($event === null) {
            return [];
        }

        $sql = "SELECT  userID
                FROM    wcf1_user_notification
                WHERE   eventID = ?
                    AND confirmTime = ?
                    AND notificationID IN (
                        SELECT  notificationID
                        FROM    wcf1_user_notification_author
                        WHERE   authorID = ?
                    )";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $event->eventID,
            0,
            $this->user->userID,
        ]);

        return $statement->fetchAll(\PDO::FETCH_COLUMN);
    }
}
