<?php

namespace wcf\system\cache\eager;

use wcf\data\box\BoxList;
use wcf\data\box\content\BoxContentList;
use wcf\system\cache\eager\data\BoxCacheData;
use wcf\system\WCF;

/**
 * Eager cache implementation for the enabled boxes and their placement on pages,
 * including the box contents of a specific language.
 *
 * Images and embedded objects of the box contents are not part of the cache,
 * because they can change independently of the boxes.
 *
 * @author Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 *
 * @extends AbstractEagerCache<BoxCacheData>
 */
final class BoxCache extends AbstractEagerCache
{
    public function __construct(
        public readonly int $languageID
    ) {}

    #[\Override]
    protected function getCacheData(): BoxCacheData
    {
        $boxList = new BoxList();
        $boxList->getConditionBuilder()->add('box.isDisabled = ?', [0]);
        $boxList->readObjects();
        $boxes = $boxList->getObjects();

        if ($boxes === []) {
            return new BoxCacheData([], [], []);
        }

        $contentList = new BoxContentList();
        $contentList->getConditionBuilder()->add('box_content.boxID IN (?)', [\array_keys($boxes)]);
        $contentList->getConditionBuilder()->add(
            '(box_content.languageID IS NULL OR box_content.languageID = ?)',
            [$this->languageID]
        );
        $contentList->readObjects();
        foreach ($contentList as $boxContent) {
            $boxes[$boxContent->boxID]->setBoxContents([$boxContent->languageID ?: 0 => $boxContent]);
        }

        $pageVisibility = [];
        $sql = "SELECT  boxID, pageID, visible
                FROM    wcf1_box_to_page";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();
        while ($row = $statement->fetchArray()) {
            if (isset($boxes[$row['boxID']])) {
                $pageVisibility[$row['pageID']][$row['boxID']] = $row['visible'] === 1;
            }
        }

        $showOrders = [];
        $sql = "SELECT  pageID, boxID, showOrder
                FROM    wcf1_page_box_order";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();
        while ($row = $statement->fetchArray()) {
            if (isset($boxes[$row['boxID']])) {
                $showOrders[$row['pageID']][$row['boxID']] = $row['showOrder'];
            }
        }

        return new BoxCacheData($boxes, $pageVisibility, $showOrders);
    }
}
