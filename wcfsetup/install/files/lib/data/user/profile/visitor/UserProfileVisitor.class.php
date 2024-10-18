<?php

namespace wcf\data\user\profile\visitor;

use wcf\data\DatabaseObject;
use wcf\system\WCF;

/**
 * Represents a user profile visitor.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int $visitorID  unique id of the user profile visitor
 * @property-read   int $ownerID    id of the user whose user profile has been visited
 * @property-read   int $userID     id of the user visiting the user profile
 * @property-read   int $time       timestamp of the (latest) visit
 */
class UserProfileVisitor extends DatabaseObject
{
    /**
     * Returns a profile visitor object or `null` if it does not exist.
     *
     * @param int $ownerID
     * @param int $userID
     * @return  UserProfileVisitor|null
     */
    public static function getObject($ownerID, $userID)
    {
        $sql = "SELECT  *
                FROM    " . static::getDatabaseTableName() . "
                WHERE   ownerID = ?
                    AND userID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$ownerID, $userID]);
        if ($row = $statement->fetchArray()) {
            return new self(null, $row);
        }

        return null;
    }
}
