<?php

namespace wcf\command\user\rank;

use wcf\data\user\rank\UserRank;
use wcf\data\user\rank\UserRankBuilder;
use wcf\event\user\rank\UserRankCreated;
use wcf\system\cache\builder\UserRankCacheBuilder;
use wcf\system\event\EventHandler;

/**
 * Creates a user rank.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class CreateUserRank
{
    public function __construct(
        private readonly UserRankBuilder $builder,
    ) {}

    public function __invoke(): UserRank
    {
        $rank = $this->builder->create();

        UserRankCacheBuilder::getInstance()->reset();

        EventHandler::getInstance()->fire(new UserRankCreated(
            $rank,
            $this->builder
        ));

        return $rank;
    }
}
