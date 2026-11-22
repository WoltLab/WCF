<?php

namespace wcf\event\user\rank;

use wcf\data\user\rank\UserRank;
use wcf\event\IPsr14Event;

/**
 * Indicates that a user rank is about to be deleted.
 * Fired before any data is removed, allowing the cleanup of linked data.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UserRankDeleting implements IPsr14Event
{
    public function __construct(
        public readonly UserRank $rank,
    ) {}
}
