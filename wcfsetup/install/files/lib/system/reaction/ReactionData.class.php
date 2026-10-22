<?php

namespace wcf\system\reaction;

use wcf\data\reaction\type\ReactionType;
use wcf\data\reaction\type\ReactionTypeCache;

/**
 * Data transfer object for an object that has reactions.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class ReactionData
{
    public function __construct(
        public readonly string $objectType,
        public readonly int $objectID,
        public readonly int $reactionTypeID = 0,
        public readonly ?string $cachedReactions = null,
        public readonly string $reactionsJson = '[]',
    ) {}

    /**
     * Returns the reaction type the active user has reacted with or `null` if there is none.
     */
    public function getReactionType(): ?ReactionType
    {
        if ($this->reactionTypeID === 0) {
            return null;
        }

        return ReactionTypeCache::getInstance()->getReactionTypeByID($this->reactionTypeID);
    }
}
