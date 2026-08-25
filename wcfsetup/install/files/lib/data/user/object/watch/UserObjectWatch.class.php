<?php

namespace wcf\data\user\object\watch;

use wcf\data\DatabaseObject;
use wcf\system\WCF;

/**
 * Represents a watched object.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int     $watchID        unique id of the watched object
 * @property-read   int     $objectTypeID   id of the `com.woltlab.wcf.user.objectWatch` object type
 * @property-read   int     $objectID       id of the watched object of the specific object type
 * @property-read   int     $userID         id of the user watching the object
 * @property-read   int     $notification   is `1` if the user wants to receive notifications for the watched object, otherwise `0`
 */
class UserObjectWatch extends DatabaseObject
{
    /**
     * Returns the UserObjectWatch with the given data or null if no such object exists.
     */
    public static function getUserObjectWatch(int $objectTypeID, int $userID, int $objectID): ?self
    {
        $sql = "SELECT  *
                FROM    wcf1_user_object_watch
                WHERE   objectTypeID = ?
                    AND userID = ?
                    AND objectID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$objectTypeID, $userID, $objectID]);

        return $statement->fetchSingleObject(self::class);
    }
}
