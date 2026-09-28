<?php

namespace wcf\event\user\rank;

use wcf\data\user\rank\UserRank;
use wcf\data\user\rank\UserRankBuilder;
use wcf\event\IPsr14Event;

/**
 * Indicates that a user rank has been created.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UserRankCreated implements IPsr14Event
{
    public function __construct(
        public readonly UserRank $rank,
        public readonly UserRankBuilder $builder,
    ) {}
}
