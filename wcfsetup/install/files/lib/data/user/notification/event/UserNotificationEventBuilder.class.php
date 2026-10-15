<?php

namespace wcf\data\user\notification\event;

use wcf\data\DatabaseObjectBuilder;

/**
 * Builder for creating and updating user notification events.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectBuilder<UserNotificationEvent>
 */
final class UserNotificationEventBuilder extends DatabaseObjectBuilder
{
    public function setPackageID(int $packageID): static
    {
        $this->properties['packageID'] = $packageID;

        return $this;
    }

    public function setEventName(string $eventName): static
    {
        $this->properties['eventName'] = $eventName;

        return $this;
    }

    public function setObjectTypeID(int $objectTypeID): static
    {
        $this->properties['objectTypeID'] = $objectTypeID;

        return $this;
    }

    public function setClassName(string $className): static
    {
        $this->properties['className'] = $className;

        return $this;
    }

    /**
     * @param string $permissions comma separated list of user group permissions
     */
    public function setPermissions(string $permissions): static
    {
        $this->properties['permissions'] = $permissions;

        return $this;
    }

    /**
     * @param string $options comma separated list of options
     */
    public function setOptions(string $options): static
    {
        $this->properties['options'] = $options;

        return $this;
    }

    public function setPreset(bool $preset): static
    {
        $this->properties['preset'] = $preset ? 1 : 0;

        return $this;
    }

    /**
     * @param 'none'|'instant'|'daily' $presetMailNotificationType
     */
    public function setPresetMailNotificationType(string $presetMailNotificationType): static
    {
        $this->properties['presetMailNotificationType'] = $presetMailNotificationType;

        return $this;
    }

    #[\Override]
    protected function getRequiredProperties(): array
    {
        return ['packageID', 'eventName', 'objectTypeID', 'className'];
    }
}
