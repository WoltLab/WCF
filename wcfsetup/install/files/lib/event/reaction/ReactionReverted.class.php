<?php

namespace wcf\event\reaction;

use wcf\data\like\Like;
use wcf\data\like\object\ILikeObject;
use wcf\event\IPsr14Event;

/**
 * Indicates that a reaction has been reverted.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class ReactionReverted implements IPsr14Event
{
    public function __construct(
        public readonly Like $like,
        public readonly ILikeObject $likeable,
    ) {}
}
