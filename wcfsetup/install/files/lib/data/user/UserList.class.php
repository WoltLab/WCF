<?php

namespace wcf\data\user;

use wcf\data\DatabaseObjectDecorator;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of users.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @template-covariant TDatabaseObject of User|DatabaseObjectDecorator<User> = User
 * @extends DatabaseObjectList<TDatabaseObject>
 */
class UserList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $className = User::class;

    public function __construct()
    {
        parent::__construct();

        if (!empty($this->sqlSelects)) {
            $this->sqlSelects .= ',';
        }
        $this->sqlSelects .= "user_option_value.*";
        $this->sqlJoins .= "
            LEFT JOIN   wcf1_user_option_value user_option_value
            ON          user_option_value.userID = user_table.userID";
    }
}
