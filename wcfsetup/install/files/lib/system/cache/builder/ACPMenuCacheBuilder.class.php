<?php

namespace wcf\system\cache\builder;

use wcf\data\acp\menu\item\ACPMenuItemList;

/**
 * Caches the ACP menu items.
 *
 * @author  Matthias Schmidt, Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class ACPMenuCacheBuilder extends AbstractCacheBuilder
{
    /**
     * @inheritDoc
     */
    public function rebuild(array $parameters)
    {
        $data = [];

        $menuItemList = new ACPMenuItemList();
        $menuItemList->sqlOrderBy = "acp_menu_item.showOrder";
        $menuItemList->readObjects();
        foreach ($menuItemList as $menuItem) {
            $data[$menuItem->parentMenuItem][] = $menuItem;
        }

        return $data;
    }
}
