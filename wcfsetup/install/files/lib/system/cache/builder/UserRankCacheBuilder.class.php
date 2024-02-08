<?php

namespace wcf\system\cache\builder;

use wcf\data\user\rank\UserRank;
use wcf\data\user\rank\UserRankList;

/**
 * Caches the list of user ranks.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class UserRankCacheBuilder extends AbstractCacheBuilder
{
    /**
     * @inheritDoc
     */
    public function rebuild(array $parameters)
    {
        $list = new UserRankList();
        $list->readObjects();

        return $list->getObjects();
    }

    public function getRank(int $rankID): ?UserRank
    {
        return $this->getData()[$rankID] ?? null;
    }
}
