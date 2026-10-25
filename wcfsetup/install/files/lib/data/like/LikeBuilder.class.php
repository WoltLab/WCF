<?php

namespace wcf\data\like;

use wcf\data\DatabaseObjectBuilder;

/**
 * Builder for creating, updating and deleting likes.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectBuilder<Like>
 */
final class LikeBuilder extends DatabaseObjectBuilder
{
    public function setObjectID(int $objectID): static
    {
        $this->properties['objectID'] = $objectID;

        return $this;
    }

    public function setObjectTypeID(int $objectTypeID): static
    {
        $this->properties['objectTypeID'] = $objectTypeID;

        return $this;
    }

    public function setObjectUserID(?int $objectUserID): static
    {
        $this->properties['objectUserID'] = $objectUserID;

        return $this;
    }

    public function setUserID(int $userID): static
    {
        $this->properties['userID'] = $userID;

        return $this;
    }

    public function setTime(int $time): static
    {
        $this->properties['time'] = $time;

        return $this;
    }

    public function setLikeValue(int $likeValue): static
    {
        $this->properties['likeValue'] = $likeValue;

        return $this;
    }

    public function setReactionTypeID(int $reactionTypeID): static
    {
        $this->properties['reactionTypeID'] = $reactionTypeID;

        return $this;
    }

    #[\Override]
    protected function getRequiredProperties(): array
    {
        return ['objectTypeID', 'objectID', 'userID', 'reactionTypeID'];
    }
}
