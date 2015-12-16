<?php
namespace wcf\system\cache\builder;
use wcf\data\menu\item\MenuItemNodeTree;
use wcf\data\menu\MenuList;

/**
 * Caches menus and menu item node trees.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
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
		foreach ($menuList as $menu) {
			$data['menus'][$menu->menuID] = $menu;
			$data['menuItems'][$menu->menuID] = new MenuItemNodeTree($menu->menuID);
		}
		
		return $data;
	}
}
