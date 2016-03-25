<?php
namespace wcf\system\cache\builder;
use wcf\data\menu\item\MenuItemList;
use wcf\data\menu\MenuList;

/**
 * Caches menus and menu item node trees.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 * @since	2.2
 */
class MenuCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @inheritDoc
	 */
	protected function rebuild(array $parameters) {
		$data = [
			'menus' => [],
			'menuItems' => []
		];
		
		$menuList = new MenuList();
		$menuList->readObjects();
		
		$menuItemList = new MenuItemList();
		$menuItemList->sqlOrderBy = "menu_item.showOrder";
		$menuItemList->readObjects();
		$menuItems = [];
		foreach ($menuItemList as $menuItem) {
			if (!isset($menuItems[$menuItem->menuID])) {
				$menuItems[$menuItem->menuID] = [];
			}
			
			$menuItems[$menuItem->menuID][$menuItem->itemID] = $menuItem;
		}
		
		foreach ($menuList as $menu) {
			$menuItemList = new MenuItemList();
			if (!empty($menuItems[$menu->menuID])) {
				$menuItemList->setMenuItems($menuItems[$menu->menuID]);
			}
			
			$data['menus'][$menu->menuID] = $menu;
			$data['menuItems'][$menu->menuID] = $menuItemList;
		}
		
		return $data;
	}
}
