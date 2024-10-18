<?php

namespace wcf\data\user;

use wcf\data\DatabaseObjectList;

/**
 * Represents a list of users.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  User        current()
 * @method  User[]      getObjects()
 * @method  User|null   getSingleObject()
 * @method  User|null   search($objectID)
 * @property    User[] $objects
 */
class UserList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $className = User::class;

    /**
     * @inheritDoc
     */
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
