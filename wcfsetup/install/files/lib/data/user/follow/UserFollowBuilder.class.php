<?php

namespace wcf\data\user\follow;

use wcf\data\DatabaseObjectBuilder;

/**
 * Builder for creating, updating and deleting followers.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectBuilder<UserFollow>
 */
final class UserFollowBuilder extends DatabaseObjectBuilder
{
    /**
     * Sets the id of the following user.
     */
    public function setUserID(int $userID): static
    {
        $this->properties['userID'] = $userID;

        return $this;
    }

    /**
     * Sets the id of the followed user.
     */
    public function setFollowUserID(int $followUserID): static
    {
        $this->properties['followUserID'] = $followUserID;

        return $this;
    }

    /**
     * Sets the time at which the following relation has been established.
     */
    public function setTime(int $time): static
    {
        $this->properties['time'] = $time;

        return $this;
    }

    #[\Override]
    protected function getRequiredProperties(): array
    {
        return ['userID', 'followUserID'];
    }
}
