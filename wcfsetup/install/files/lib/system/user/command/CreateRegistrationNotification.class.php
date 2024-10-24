<?php

namespace wcf\system\user\command;

use wcf\data\user\User;
use wcf\system\user\notification\object\UserRegistrationUserNotificationObject;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\WCF;

/**
 * Send a notification of user registration status.
 *
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class CreateRegistrationNotification
{
    public function __construct(private readonly User $user)
    {
    }

    public function __invoke(): void
    {
        if ($this->user->requiresEmailActivation()) {
            return;
        }

        $recipientIDs = $this->getRecipientsForNotificationEvent();
        if (!empty($recipientIDs)) {
            UserNotificationHandler::getInstance()->fireEvent(
                $this->user->requiresAdminActivation() ? 'needActivation' : 'success',
                'com.woltlab.wcf.user.registration.notification',
                new UserRegistrationUserNotificationObject($this->user),
                $recipientIDs
            );
        }
    }

    /**
     * @return int[]
     */
    private function getRecipientsForNotificationEvent(): array
    {
        $sql = "SELECT  userID
                FROM    wcf1_user_to_group
                WHERE   groupID IN (
                        SELECT  groupID
                        FROM    wcf1_user_group_option_value
                        WHERE   optionID IN (
                                    SELECT  optionID
                                    FROM    wcf1_user_group_option
                                    WHERE   optionName = ?
                                )
                            AND optionValue = ?
                    )";
        $statement = WCF::getDB()->prepare($sql, 100);
        $statement->execute([
            'admin.user.canSearchUser',
            1,
        ]);

        return $statement->fetchAll(\PDO::FETCH_COLUMN);
    }
}
