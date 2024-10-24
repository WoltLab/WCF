<?php

namespace wcf\data\user\follow;

use wcf\data\DatabaseObject;
use wcf\system\WCF;

/**
 * Represents a user's follower.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int $followID       unique id of the following relation
 * @property-read   int $userID         id of the following user
 * @property-read   int $followUserID       id of the followed user
 * @property-read   int $time           time at which following relation has been established
 */
class UserFollow extends DatabaseObject
{
    /**
     * Retrieves a follower.
     *
     * @param int $userID
     * @param int $followUserID
     * @return  UserFollow
     */
    public static function getFollow($userID, $followUserID)
    {
        $sql = "SELECT  followID
                FROM    wcf1_user_follow
                WHERE   userID = ?
                    AND followUserID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $userID,
            $followUserID,
        ]);

        $row = $statement->fetchArray();
        if (!$row) {
            $row = [];
        }

        return new self(null, $row);
    }
}
