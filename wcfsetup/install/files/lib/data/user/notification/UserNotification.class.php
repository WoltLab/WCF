<?php

namespace wcf\data\user\notification;

use wcf\data\DatabaseObject;
use wcf\system\WCF;

/**
 * Represents a user notification.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int $notificationID     unique id of the user notification
 * @property-read   int $packageID      deprecated
 * @property-read   int $eventID        id of the user notification event the user notification belongs to
 * @property-read   int $objectID       id of the object that triggered the user notification
 * @property-read   int $baseObjectID       id of a generic base object of object that triggered the user notification or 0 if there is no such base object
 * @property-read   string $eventHash      hash of the event the user notification represents, is used to stack notifications
 * @property-read   int|null $authorID       id of the user that triggered the user notification or null if there is no such user or the user was a guest
 * @property-read   int $timesTriggered     total number of times a stacked notification has been triggered by registered users and guests
 * @property-read   int $guestTimesTriggered    number of times a stacked notification has been triggered by guests
 * @property-read   int $userID         id of the user who receives the user notification
 * @property-read   int $time           timestamp at which the user notification has been created
 * @property-read   int $mailNotified       is 0 has not be notified by mail about the user notification, otherwise 1
 * @property-read   int $confirmTime        timestamp at which the user notification has been marked as confirmed/read
 * @property-read   array $additionalData     array with additional data of the user notification event
 */
class UserNotification extends DatabaseObject
{
    /**
     * @inheritDoc
     */
    public function __get($name)
    {
        $value = parent::__get($name);

        // treat additional data as data variables if it is an array
        if ($value === null && isset($this->data['additionalData'][$name])) {
            $value = $this->data['additionalData'][$name];
        }

        return $value;
    }

    /**
     * @inheritDoc
     */
    protected function handleData($data)
    {
        parent::handleData($data);

        $this->data['additionalData'] = @\unserialize($this->data['additionalData'] ?: '');
        if (!\is_array($this->data['additionalData'])) {
            $this->data['additionalData'] = [];
        }
    }

    /**
     * Returns an existing notification.
     *
     * @param int $packageID
     * @param int $eventID
     * @param int $objectID
     * @return  UserNotification|null
     */
    public static function getNotification($packageID, $eventID, $objectID)
    {
        $sql = "SELECT  *
                FROM    wcf1_user_notification
                WHERE   packageID = ?
                    AND eventID = ?
                    AND objectID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$packageID, $eventID, $objectID]);
        $row = $statement->fetchArray();
        if ($row !== false) {
            return new self(null, $row);
        }

        return null;
    }
}
