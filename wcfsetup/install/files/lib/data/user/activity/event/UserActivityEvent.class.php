<?php

namespace wcf\data\user\activity\event;

use wcf\data\DatabaseObject;

/**
 * Represents a user's activity.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int $eventID        unique id of the user activity event
 * @property-read   int $objectTypeID       id of the `com.woltlab.wcf.user.recentActivityEvent` object type
 * @property-read   int $objectID       id of the object the user activity event belongs to
 * @property-read   int|null $languageID     id of the language of the related object or null if the object has no specific language
 * @property-read   int $userID         id of the user who has triggered the user activity event
 * @property-read   int $time           timestamp at which the user activity event has been triggered
 * @property-read   array $additionalData     array with additional data of the user activity event
 */
class UserActivityEvent extends DatabaseObject
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

        $this->data['additionalData'] = @\unserialize($this->data['additionalData']);
        if (!\is_array($this->data['additionalData'])) {
            $this->data['additionalData'] = [];
        }
    }
}
