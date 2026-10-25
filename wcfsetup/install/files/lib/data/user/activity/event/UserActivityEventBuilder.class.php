<?php

namespace wcf\data\user\activity\event;

use wcf\data\DatabaseObjectBuilder;

/**
 * Builder for creating, updating and deleting user activity events.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectBuilder<UserActivityEvent>
 */
final class UserActivityEventBuilder extends DatabaseObjectBuilder
{
    /**
     * Sets the id of the `com.woltlab.wcf.user.recentActivityEvent` object type.
     */
    public function setObjectTypeID(int $objectTypeID): static
    {
        $this->properties['objectTypeID'] = $objectTypeID;

        return $this;
    }

    /**
     * Sets the id of the object the user activity event belongs to.
     */
    public function setObjectID(int $objectID): static
    {
        $this->properties['objectID'] = $objectID;

        return $this;
    }

    /**
     * Sets the id of the language of the related object or `null` if the
     * object has no specific language.
     */
    public function setLanguageID(?int $languageID): static
    {
        $this->properties['languageID'] = $languageID;

        return $this;
    }

    /**
     * Sets the id of the user who has triggered the user activity event or
     * `null` if the event was triggered by a guest.
     */
    public function setUserID(?int $userID): static
    {
        $this->properties['userID'] = $userID;

        return $this;
    }

    /**
     * Sets the name of the guest who has triggered the user activity event or
     * `null` if the event was triggered by a registered user.
     */
    public function setUsername(?string $username): static
    {
        $this->properties['username'] = $username;

        return $this;
    }

    /**
     * Sets the time at which the user activity event has been triggered.
     */
    public function setTime(int $time): static
    {
        $this->properties['time'] = $time;

        return $this;
    }

    /**
     * Sets the additional data of the user activity event.
     *
     * @param mixed[] $additionalData
     */
    public function setAdditionalData(array $additionalData): static
    {
        $this->properties['additionalData'] = \serialize($additionalData);

        return $this;
    }

    #[\Override]
    protected function getRequiredProperties(): array
    {
        return ['objectTypeID', 'objectID', 'time'];
    }
}
