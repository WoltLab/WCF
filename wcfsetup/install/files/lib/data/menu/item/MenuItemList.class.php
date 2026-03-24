<?php

namespace wcf\data\menu\item;

use wcf\data\DatabaseObjectList;

/**
 * Represents a list of menu items.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends DatabaseObjectList<MenuItem>
 */
class MenuItemList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $className = MenuItem::class;

    /**
     * Sets the menu items used to improve menu cache performance.
     *
     * @param MenuItem[] $menuItems list of menu item objects
     * @return void
     */
    public function setMenuItems(array $menuItems)
    {
        $this->objects = $menuItems;
        $this->indexToObject = $this->objectIDs = \array_keys($this->objects);
    }
}
