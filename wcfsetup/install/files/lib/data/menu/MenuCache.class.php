<?php
namespace wcf\data\menu;
use wcf\data\menu\item\MenuItemList;
use wcf\system\cache\builder\MenuCacheBuilder;
use wcf\system\SingletonFactory;

/**
 * Manages the menu cache.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Menu
 * @since	3.0
 */
class MenuCache extends SingletonFactory {
	/**
	 * @var	Menu[]
	 */
	protected $cachedMenus;
	
	/**
	 * @var	MenuItemList[]
	 */
	protected $cachedMenuItems;
	
	/**
	 * @inheritDoc
	 */
	protected function init() {
		$this->cachedMenus = MenuCacheBuilder::getInstance()->getData([], 'menus');
		$this->cachedMenuItems = MenuCacheBuilder::getInstance()->getData([], 'menuItems');
	}
	
	/**
	 * Returns a menu by id.
	 * 
	 * @param	integer		$menuID		menu id
	 * @return	Menu|null	menu object or null if menu id is unknown
	 */
	public function getMenuByID($menuID) {
		if (isset($this->cachedMenus[$menuID])) {
			return $this->cachedMenus[$menuID];
		}
		
		return null;
	}
	
	/**
	 * Returns a menu item list by menu id.
	 * 
	 * @param	integer			$menuID		menu id
	 * @return	MenuItemList|null	menu item list object or null if menu id is unknown
	 */
	public function getMenuItemsByMenuID($menuID) {
		if (isset($this->cachedMenuItems[$menuID])) {
			return $this->cachedMenuItems[$menuID];
		}
		
		return null;
	}
	
	/**
	 * Returns the main menu or null.
	 * 
	 * @return	Menu|null	menu object
	 */
	public function getMainMenu() {
		return $this->getMenuByID(MenuCacheBuilder::getInstance()->getData([], 'mainMenuID'));
	}
}
