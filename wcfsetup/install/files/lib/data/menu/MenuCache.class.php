<?php
namespace wcf\data\menu;
use wcf\data\menu\item\MenuItemNodeTree;
use wcf\system\cache\builder\MenuCacheBuilder;
use wcf\system\SingletonFactory;

/**
 * Manages the menu cache.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.menu
 * @category	Community Framework
 */
class MenuCache extends SingletonFactory {
	/**
	 * @var Menu[]
	 */
	protected $cachedMenus;
	
	/**
	 * @var MenuItemNodeTree[]
	 */
	protected $cachedMenuItems;
	
	/**
	 * @inheritDoc
	 */
	protected function init() {
		$this->cachedMenus = MenuCacheBuilder::getInstance()->getData([], 'menus');
		$this->cachedMenuItems = MenuCacheBuilder::getInstance()->getData([], 'menuItems');
	}
	
	public function getMenuByID($menuID) {
		if (isset($this->cachedMenus[$menuID])) {
			return $this->cachedMenus[$menuID];
		}
		
		return null;
	}
	
	public function getMenuItemsByMenuID($menuID) {
		if (isset($this->cachedMenuItems[$menuID])) {
			return $this->cachedMenuItems[$menuID];
		}
		
		return null;
	}
}
