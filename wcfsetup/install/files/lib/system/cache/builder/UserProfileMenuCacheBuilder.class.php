<?php

namespace wcf\system\cache\builder;

use wcf\data\user\profile\menu\item\UserProfileMenuItemList;

/**
 * Caches the user profile menu items.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserProfileMenuCacheBuilder extends AbstractCacheBuilder
{
    /**
     * @inheritDoc
     */
    protected function rebuild(array $parameters)
    {
        $itemList = new UserProfileMenuItemList();
        $itemList->sqlOrderBy = "user_profile_menu_item.showOrder ASC";
        $itemList->readObjects();

        return $itemList->getObjects();
    }
}
