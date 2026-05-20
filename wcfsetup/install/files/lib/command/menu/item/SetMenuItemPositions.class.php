<?php

namespace wcf\command\menu\item;

use wcf\data\menu\item\MenuItemEditor;
use wcf\system\WCF;

/**
 * Sets the position of menu items.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class SetMenuItemPositions
{
    /**
     * @param array<int, list<int>> $positions
     */
    public function __construct(private readonly array $positions) {}

    public function __invoke(): void
    {
        $sql = "UPDATE  wcf1_menu_item
                SET     parentItemID = ?,
                        showOrder = ?
                WHERE   itemID = ?";
        $statement = WCF::getDB()->prepare($sql);

        WCF::getDB()->beginTransaction();
        foreach ($this->positions as $parentItemID => $children) {
            foreach ($children as $showOrder => $menuItemID) {
                $statement->execute([
                    $parentItemID ?: null,
                    $showOrder + 1,
                    $menuItemID,
                ]);
            }
        }
        WCF::getDB()->commitTransaction();

        MenuItemEditor::resetCache();
    }
}
