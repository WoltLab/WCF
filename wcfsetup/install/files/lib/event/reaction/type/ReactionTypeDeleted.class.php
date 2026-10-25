<?php

namespace wcf\event\reaction\type;

use wcf\data\reaction\type\ReactionType;
use wcf\event\IPsr14Event;

/**
 * Indicates that a reaction type has been deleted.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class ReactionTypeDeleted implements IPsr14Event
{
    public function __construct(
        public readonly ReactionType $reactionType,
    ) {}
}
