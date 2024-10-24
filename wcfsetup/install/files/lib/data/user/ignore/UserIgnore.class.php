<?php

namespace wcf\data\user\ignore;

use wcf\data\DatabaseObject;
use wcf\system\WCF;

/**
 * Represents an ignored user.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int $ignoreID       unique id of the ignore relation
 * @property-read   int $userID         id of the ignoring user
 * @property-read   int $ignoreUserID       id of the ignored user
 * @property-read   int $time           time at which ignore relation has been established
 * @property-read   int $type           one of the TYPE_* class constants
 */
class UserIgnore extends DatabaseObject
{
    public const TYPE_NO_IGNORE = 0;

    public const TYPE_BLOCK_DIRECT_CONTACT = 1;

    public const TYPE_HIDE_MESSAGES = 2;

    /**
     * Returns a UserIgnore object for given ignored user id.
     *
     * @param int $ignoreUserID
     * @return  UserIgnore
     */
    public static function getIgnore($ignoreUserID)
    {
        $sql = "SELECT  *
                FROM    wcf1_user_ignore
                WHERE   userID = ?
                    AND ignoreUserID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            WCF::getUser()->userID,
            $ignoreUserID,
        ]);

        $row = $statement->fetchArray();
        if (!$row) {
            $row = [];
        }

        return new self(null, $row);
    }
}
