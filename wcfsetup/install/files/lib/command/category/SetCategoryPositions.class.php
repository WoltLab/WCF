<?php

namespace wcf\command\category;

use wcf\data\category\CategoryEditor;
use wcf\system\category\CategoryHandler;
use wcf\system\WCF;

/**
 * Sets the positions of categories.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class SetCategoryPositions
{
    /**
     * @param array<int, list<int>> $positions
     */
    public function __construct(private readonly array $positions) {}

    public function __invoke(): void
    {
        $parentUpdates = [];
        $objectType = null;

        $sql = "UPDATE  wcf1_category
                SET     parentCategoryID = ?,
                        showOrder = ?
                WHERE   categoryID = ?";
        $statement = WCF::getDB()->prepare($sql);

        WCF::getDB()->beginTransaction();
        foreach ($this->positions as $parentCategoryID => $children) {
            foreach ($children as $showOrder => $categoryID) {
                $category = CategoryHandler::getInstance()->getCategory($categoryID);
                if ($category === null) {
                    continue;
                }

                if ($objectType === null) {
                    $objectType = $category->getObjectType();
                }

                if ($category->parentCategoryID !== $parentCategoryID) {
                    $parentUpdates[$categoryID] = [
                        'oldParentCategoryID' => $category->parentCategoryID,
                        'newParentCategoryID' => $parentCategoryID,
                    ];
                }

                $statement->execute([
                    $parentCategoryID,
                    $showOrder + 1,
                    $categoryID,
                ]);
            }
        }
        WCF::getDB()->commitTransaction();

        CategoryEditor::resetCache();

        if ($parentUpdates !== [] && $objectType !== null) {
            $objectType->getProcessor()->changedParentCategories($parentUpdates);
        }
    }
}
