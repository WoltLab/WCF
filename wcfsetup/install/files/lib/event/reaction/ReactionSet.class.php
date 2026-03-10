<?php

namespace wcf\event\reaction;

use wcf\data\like\object\ILikeObject;
use wcf\data\reaction\type\ReactionType;
use wcf\data\user\User;
use wcf\event\IPsr14Event;

/**
 * Indicates that a reaction has been set.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class ReactionSet implements IPsr14Event
{
    public function __construct(
        public readonly ILikeObject $likeable,
        public readonly User $user,
        public readonly ReactionType $reactionType
    ) {}
}
