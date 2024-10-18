<?php

namespace wcf\system\cache\builder;

use wcf\data\user\notification\event\UserNotificationEvent;
use wcf\system\WCF;

/**
 * Caches user notification events.
 *
 * @author  Marcell Werk, Oliver Kliebisch
 * @copyright   2001-2019 WoltLab GmbH, Oliver Kliebisch
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserNotificationEventCacheBuilder extends AbstractCacheBuilder
{
    /**
     * @inheritDoc
     */
    protected function rebuild(array $parameters)
    {
        $data = [];

        // get events
        $sql = "SELECT      event.*, object_type.objectType
                FROM        wcf1_user_notification_event event
                LEFT JOIN   wcf1_object_type object_type
                ON          object_type.objectTypeID = event.objectTypeID";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();
        while ($row = $statement->fetchArray()) {
            if (!isset($data[$row['objectType']])) {
                $data[$row['objectType']] = [];
            }

            if (!isset($data[$row['objectType']][$row['eventName']])) {
                $databaseObject = new UserNotificationEvent(null, $row);
                $data[$row['objectType']][$row['eventName']] = $databaseObject->getProcessor();
            }
        }

        return $data;
    }
}
