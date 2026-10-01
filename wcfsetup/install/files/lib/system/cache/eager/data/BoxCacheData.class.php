<?php

namespace wcf\system\cache\eager\data;

use wcf\data\box\Box;

/**
 * @author Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class BoxCacheData
{
    /**
     * @param array<int, Box> $boxes
     * @param array<int, array<int, bool>> $pageVisibility
     * @param array<int, array<int, int>> $showOrders
     */
    public function __construct(
        public readonly array $boxes,
        public readonly array $pageVisibility,
        public readonly array $showOrders,
    ) {}

    /**
     * Returns the boxes that are placed on the given page. The page id `0`
     * returns the boxes that are visible everywhere.
     *
     * @return array<int, Box>
     */
    public function getBoxesForPage(int $pageID): array
    {
        $boxes = [];
        foreach ($this->boxes as $boxID => $box) {
            // An explicit assignment to the page takes precedence over `visibleEverywhere`.
            if ($this->pageVisibility[$pageID][$boxID] ?? ($box->visibleEverywhere === 1)) {
                $boxes[$boxID] = $box;
            }
        }

        return $boxes;
    }

    /**
     * Returns the custom show order of the boxes on the given page.
     *
     * @return array<int, int>
     */
    public function getShowOrders(int $pageID): array
    {
        return $this->showOrders[$pageID] ?? [];
    }
}
