<?php

namespace wcf\system\cache\eager\data;

use wcf\data\trophy\Trophy;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class TrophyCacheData
{
    public function __construct(
        /** @var array<int, Trophy> */
        public readonly array $trophies,
        /** @var array<int, Trophy> */
        public readonly array $enabledTrophies,
        /** @var array<int, array<int, Trophy>> */
        public readonly array $categorySortedTrophies = [],
    ) {
    }

    public function getTrophyByID(int $trophyID): ?Trophy
    {
        return $this->trophies[$trophyID] ?? null;
    }

    /**
     * @return Trophy[]
     */
    public function getTrophiesByCategoryID(int $categoryID): array
    {
        return $this->categorySortedTrophies[$categoryID] ?? [];
    }
}