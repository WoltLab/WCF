<?php

namespace wcf\data\trophy;

use wcf\system\cache\eager\data\TrophyCacheData;
use wcf\system\cache\runtime\FileRuntimeCache;
use wcf\system\SingletonFactory;

/**
 * Trophy cache management.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.1
 */
final class TrophyCache extends SingletonFactory
{
    private TrophyCacheData $trophyCache;

    /**
     * @inheritDoc
     */
    public function init()
    {
        $this->trophyCache = (new \wcf\system\cache\eager\TrophyCache())->getCache();
    }

    /**
     * Returns the trophy with the given trophyID.
     */
    public function getTrophyByID(int $trophyID): ?Trophy
    {
        return $this->trophyCache->getTrophyByID($trophyID);
    }

    /**
     * Returns the trophy with the given trophyID.
     *
     * @param int[] $trophyIDs
     * @return Trophy[]
     */
    public function getTrophiesByID(array $trophyIDs): array
    {
        $returnValues = [];

        foreach ($trophyIDs as $trophyID) {
            $returnValues[] = $this->getTrophyByID($trophyID);
        }

        return $returnValues;
    }

    /**
     * Returns all trophies for a specific category.
     *
     * @return  Trophy[]
     */
    public function getTrophiesByCategoryID(int $categoryID): array
    {
        return $this->trophyCache->getTrophiesByCategoryID($categoryID);
    }

    /**
     * Returns all enabled trophies for a specific category.
     *
     * @return  Trophy[]
     */
    public function getEnabledTrophiesByCategoryID(int $categoryID): array
    {
        $trophies = $this->getTrophiesByCategoryID($categoryID);

        $returnValues = [];
        foreach ($trophies as $trophy) {
            if (!$trophy->isDisabled) {
                $returnValues[$trophy->getObjectID()] = $trophy;
            }
        }

        return $returnValues;
    }

    /**
     * Return all trophies.
     *
     * @return  Trophy[]
     */
    public function getTrophies(): array
    {
        return $this->trophyCache->trophies;
    }

    /**
     * Return all enabled trophies.
     *
     * @return  Trophy[]
     */
    public function getEnabledTrophies(): array
    {
        return $this->trophyCache->enabledTrophies;
    }

    /**
     * @param Trophy[] $trophies
     */
    public function cacheFileIDs(array $trophies): void
    {
        $fileIDs = [];
        foreach ($trophies as $trophy) {
            if ($trophy->imageFileID) {
                $fileIDs[] = $trophy->imageFileID;
            }
        }

        FileRuntimeCache::getInstance()->cacheObjectIDs($fileIDs);
    }

    /**
     * Resets the cache for the trophies.
     */
    public function clearCache(): void
    {
        (new \wcf\system\cache\eager\TrophyCache())->rebuild();
    }
}
