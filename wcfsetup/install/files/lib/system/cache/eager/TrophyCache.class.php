<?php

namespace wcf\system\cache\eager;

use wcf\data\trophy\TrophyList;
use wcf\system\cache\eager\data\TrophyCacheData;

/**
 * Caches for trophies.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 *
 * @extends AbstractEagerCache<TrophyCacheData>
 */
final class TrophyCache extends AbstractEagerCache
{
    #[\Override]
    protected function getCacheData(): TrophyCacheData
    {
        $trophyList = new TrophyList();
        $trophyList->sqlOrderBy = 'showOrder ASC';
        $trophyList->readObjects();

        $trophies = $trophyList->getObjects();
        $enabledTrophies = \array_filter($trophies, static function ($trophy) {
            return !$trophy->isDisabled;
        });

        $categorySortedTrophies = [];
        foreach ($trophies as $trophy) {
            if (!isset($categorySortedTrophies[$trophy->categoryID])) {
                $categorySortedTrophies[$trophy->categoryID] = [];
            }

            $categorySortedTrophies[$trophy->categoryID][$trophy->getObjectID()] = $trophy;
        }

        return new TrophyCacheData(
            $trophies,
            $enabledTrophies,
            $categorySortedTrophies
        );
    }
}
