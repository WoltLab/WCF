<?php

namespace wcf\data\user\notification\event\recipient;

use wcf\data\user\UserList;

/**
 * Extends the user list to provide special functions for handling recipients of user notifications.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserNotificationEventRecipientList extends UserList
{
    /**
     * @inheritDoc
     */
    public function __construct()
    {
        $this->sqlJoins = "
            LEFT JOIN   wcf1_user user_table
            ON          user_table.userID = event_to_user.userID";
        $this->sqlSelects = 'user_table.*';

        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    public function getDatabaseTableName()
    {
        return 'wcf1_user_notification_event_to_user';
    }

    /**
     * @inheritDoc
     */
    public function getDatabaseTableAlias()
    {
        return 'event_to_user';
    }
}
