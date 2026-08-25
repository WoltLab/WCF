<?php

namespace wcf\data\user\object\watch;

use wcf\data\DatabaseObjectBuilder;
use wcf\data\object\type\ObjectType;
use wcf\data\user\User;

/**
 * Builder for creating, updating and deleting watched objects.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectBuilder<UserObjectWatch>
 */
final class UserObjectWatchBuilder extends DatabaseObjectBuilder
{
    /**
     * Sets the object type of the watched object, must be an object type of the
     * definition `com.woltlab.wcf.user.objectWatch`.
     */
    public function setObjectType(ObjectType $objectType): static
    {
        return $this->setObjectTypeID($objectType->objectTypeID);
    }

    /**
     * Sets the id of the object type of the watched object.
     */
    public function setObjectTypeID(int $objectTypeID): static
    {
        $this->properties['objectTypeID'] = $objectTypeID;

        return $this;
    }

    /**
     * Sets the id of the watched object.
     */
    public function setObjectID(int $objectID): static
    {
        $this->properties['objectID'] = $objectID;

        return $this;
    }

    /**
     * Sets the user watching the object.
     */
    public function setUser(User $user): static
    {
        return $this->setUserID($user->userID);
    }

    /**
     * Sets the id of the user watching the object.
     */
    public function setUserID(int $userID): static
    {
        $this->properties['userID'] = $userID;

        return $this;
    }

    /**
     * Sets whether the user wants to receive notifications for the watched object.
     */
    public function setNotification(bool $notification): static
    {
        $this->properties['notification'] = $notification ? 1 : 0;

        return $this;
    }

    #[\Override]
    protected function getRequiredProperties(): array
    {
        return ['objectTypeID', 'objectID', 'userID'];
    }
}
