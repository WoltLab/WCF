<?php

namespace wcf\data\user\profile\visitor;

use wcf\data\DatabaseObjectBuilder;
use wcf\data\user\User;

/**
 * Builder for creating, updating and deleting user profile visitors.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectBuilder<UserProfileVisitor>
 */
final class UserProfileVisitorBuilder extends DatabaseObjectBuilder
{
    /**
     * Sets the user whose profile has been visited.
     */
    public function setOwner(User $owner): static
    {
        $this->properties['ownerID'] = $owner->userID;

        return $this;
    }

    /**
     * Sets the user that visited the profile.
     */
    public function setUser(User $user): static
    {
        $this->properties['userID'] = $user->userID;

        return $this;
    }

    /**
     * Sets the timestamp of the (latest) visit.
     */
    public function setTime(int $time): static
    {
        $this->properties['time'] = $time;

        return $this;
    }

    #[\Override]
    protected function getRequiredProperties(): array
    {
        return ['ownerID', 'userID', 'time'];
    }
}
