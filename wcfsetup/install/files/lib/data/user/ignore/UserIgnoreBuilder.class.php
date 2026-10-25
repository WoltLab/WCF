<?php

namespace wcf\data\user\ignore;

use wcf\data\DatabaseObjectBuilder;

/**
 * Builder for creating, updating and deleting ignored users.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectBuilder<UserIgnore>
 */
final class UserIgnoreBuilder extends DatabaseObjectBuilder
{
    /**
     * Sets the id of the ignoring user.
     */
    public function setUserID(int $userID): static
    {
        $this->properties['userID'] = $userID;

        return $this;
    }

    /**
     * Sets the id of the ignored user.
     */
    public function setIgnoreUserID(int $ignoreUserID): static
    {
        $this->properties['ignoreUserID'] = $ignoreUserID;

        return $this;
    }

    /**
     * @param UserIgnore::TYPE_BLOCK_DIRECT_CONTACT|UserIgnore::TYPE_HIDE_MESSAGES $type
     */
    public function setType(int $type): static
    {
        $this->properties['type'] = $type;

        return $this;
    }

    /**
     * Sets the time at which the ignore relation has been established.
     */
    public function setTime(int $time): static
    {
        $this->properties['time'] = $time;

        return $this;
    }

    #[\Override]
    protected function getRequiredProperties(): array
    {
        return ['userID', 'ignoreUserID'];
    }
}
