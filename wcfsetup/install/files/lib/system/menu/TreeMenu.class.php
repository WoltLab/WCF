<?php
namespace wcf\system\menu;
use wcf\system\event\EventHandler;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Basis class for a tree menu.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.menu
 * @category	Community Framework
 */
abstract class TreeMenu extends SingletonFactory {
	/**
	 * list of visible menu items
	 * @var	array<wcf\system\menu\ITreeMenuItem>
	 */
	public $menuItemList = array();
	
	/**
	 * list of the names of the active menu items
	 * @var	array<string>
	 */
	public $activeMenuItems = array();
	
	/**
	 * list of all menu items
	 * @var	array<wcf\system\menu\ITreeMenuItem>
	 */
	public $menuItems = null;
	
	/**
	 * @see	wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		// get menu items from cache
		$this->loadCache();
		
		// check menu items
		$this->checkMenuItems();
		
		// remove items without children
		$this->removeEmptyItems();
		
		// build plain menu item list
		$this->buildMenuItemList();
		
		// call init event
		EventHandler::getInstance()->fireAction($this, 'init');
	}
	
	/**
	 * Loads cached menu items.
	 */
	protected function loadCache() {
		// call loadCache event
		EventHandler::getInstance()->fireAction($this, 'loadCache');
		
		$this->menuItems = array();
	}
	
	/**
	 * Checks the options and permissions of given menu item.
	 * 
	 * @param	wcf\system\menu\ITreeMenuItem		$item
	 * @return	boolean
	 */
	protected function checkMenuItem(ITreeMenuItem $item) {
		// check the options of this item
		$hasEnabledOption = true;
		if (!empty($item->options)) {
			$hasEnabledOption = false;
			$options = explode(',', strtoupper($item->options));
			foreach ($options as $option) {
				if (defined($option) && constant($option)) {
					$hasEnabledOption = true;
					break;
				}
			}
		}
		if (!$hasEnabledOption) return false;
		
		// check the permission of this item for the active user
		$hasPermission = true;
		if (!empty($item->permissions)) {
			$hasPermission = false;
			$permissions = explode(',', $item->permissions);
			foreach ($permissions as $permission) {
				if (WCF::getSession()->getPermission($permission)) {
					$hasPermission = true;
					break;
				}
			}
		}
		if (!$hasPermission) return false;
		
		return true;
	}
	
	/**
	 * Checks the options and permissions of the menu items.
	 * 
	 * @param	string		$parentMenuItem
	 */
	protected function checkMenuItems($parentMenuItem = '') {
		if (!isset($this->menuItems[$parentMenuItem])) return;
		
		foreach ($this->menuItems[$parentMenuItem] as $key => $item) {
			if ($this->checkMenuItem($item)) {
				// check children
				$this->checkMenuItems($item->menuItem);
			}
			else {
				// remove this item
				unset($this->menuItems[$parentMenuItem][$key]);
			}
		}
	}
	
	/**
	 * Removes items without children.
	 * 
	 * @param	string		$parentMenuItem
	 */
	protected function removeEmptyItems($parentMenuItem = '') {
		if (!isset($this->menuItems[$parentMenuItem])) return;
		
		foreach ($this->menuItems[$parentMenuItem] as $key => $item) {
			$this->removeEmptyItems($item->menuItem);
			if (empty($item->menuItemLink) && empty($item->menuItemController) && (!isset($this->menuItems[$item->menuItem]) || empty($this->menuItems[$item->menuItem]))) {
				// remove this item
				unset($this->menuItems[$parentMenuItem][$key]);
			}
		}
	}
	
	/**
	 * Builds a plain menu item list.
	 * 
	 * @param	string		$parentMenuItem
	 */
	protected function buildMenuItemList($parentMenuItem = '') {
		if (!isset($this->menuItems[$parentMenuItem])) return;
		
		foreach ($this->menuItems[$parentMenuItem] as $item) {
			$this->menuItemList[$item->menuItem] = $item;
			$this->buildMenuItemList($item->menuItem);
		}
	}
	
	/**
	 * Sets the active menu item.
	 * This should be done before the menu.tpl template calls the function getMenu().
	 * 
	 * This function should be used in each script which uses a template that includes the menu.tpl.
	 * 
	 * @param	string		$menuItem	name of the active menu item
	 */
	public function setActiveMenuItem($menuItem) {
		$this->activeMenuItems = array();
		
		// build active menu list
		while (isset($this->menuItemList[$menuItem])) {
			$this->activeMenuItems[] = $menuItem;
			$menuItem = $this->menuItemList[$menuItem]->parentMenuItem;
		}
	}
	
	/**
	 * Returns a list of the active menu items.
	 * 
	 * @return	array
	 */
	public function getActiveMenuItems() {
		return $this->activeMenuItems;
	}
	
	/**
	 * Returns the active menu item.
	 * 
	 * @param	integer		$level
	 * @return	string
	 */
	public function getActiveMenuItem($level = 0) {
		if ($level < count($this->activeMenuItems)) {
			return $this->activeMenuItems[(count($this->activeMenuItems) - ($level + 1))];
		}
		return null;
	}
	
	/**
	 * Returns the list of menu items.
	 * 
	 * @param	string		$parentMenuItem
	 * @return	array
	 */
	public function getMenuItems($parentMenuItem = null) {
		if ($parentMenuItem === null) return $this->menuItems;
		if (isset($this->menuItems[$parentMenuItem])) return $this->menuItems[$parentMenuItem];
		return array();
	}
}
