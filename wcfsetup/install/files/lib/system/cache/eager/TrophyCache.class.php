<?php

namespace wcf\system\cache\eager;

use wcf\data\trophy\Trophy;
use wcf\system\cache\eager\data\TrophyCacheData;
use wcf\system\WCF;

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
        // `TrophyList` cannot be used, otherwise calling `Trophy::getFile()` would load the files for all existing trophies
        // and not just those that are actually needed.
        $sql = "SELECT   *
                FROM     wcf1_trophy
                ORDER BY showOrder ASC";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();

        $trophies = [];
        while ($trophy = $statement->fetchObject(Trophy::class)) {
            $trophies[$trophy->trophyID] = $trophy;
        }

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
