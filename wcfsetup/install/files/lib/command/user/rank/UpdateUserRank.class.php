<?php

namespace wcf\command\user\rank;

use wcf\data\user\rank\UserRank;
use wcf\data\user\rank\UserRankBuilder;
use wcf\event\user\rank\UserRankUpdated;
use wcf\system\cache\builder\UserRankCacheBuilder;
use wcf\system\event\EventHandler;

/**
 * Updates a user rank.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UpdateUserRank
{
    public function __construct(
        private readonly UserRankBuilder $builder,
    ) {}

    public function __invoke(): UserRank
    {
        $rank = $this->builder->update();

        UserRankCacheBuilder::getInstance()->reset();

        EventHandler::getInstance()->fire(new UserRankUpdated(
            $rank,
            $this->builder
        ));

        return $rank;
    }
}
