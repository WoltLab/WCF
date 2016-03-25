<?php
namespace wcf\system\menu\page;
use wcf\data\ProcessibleDatabaseObject;
use wcf\system\cache\builder\PageMenuCacheBuilder;
use wcf\system\event\EventHandler;
use wcf\system\exception\SystemException;
use wcf\system\menu\ITreeMenuItem;
use wcf\system\menu\TreeMenu;

/**
 * Builds the page menu.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.menu.page
 * @category	Community Framework
 */
class PageMenu extends TreeMenu {
	/**
	 * @inheritDoc
	 * @throws	SystemException
	 */
	protected function init() {
		// get menu items from cache
		$this->loadCache();
		
		// check menu items
		$this->checkMenuItems('header');
		$this->checkMenuItems('footer');
		
		// build plain menu item list
		$this->buildMenuItemList('header');
		$this->buildMenuItemList('footer');
		
		// call init event
		EventHandler::getInstance()->fireAction($this, 'init');
	}
	
	/**
	 * @inheritDoc
	 */
	protected function loadCache() {
		parent::loadCache();
		
		// get cache
		$this->menuItems = PageMenuCacheBuilder::getInstance()->getData();
	}
	
	/**
	 * @inheritDoc
	 */
	protected function checkMenuItem(ITreeMenuItem $item) {
		if (!parent::checkMenuItem($item)) return false;
		
		if ($item instanceof ProcessibleDatabaseObject && $item->getProcessor() instanceof IPageMenuItemProvider) {
			return $item->getProcessor()->isVisible();
		}
		
		return true;
	}
	
	/**
	 * @inheritDoc
	 */
	public function setActiveMenuItem($menuItem) {
		if (isset($this->menuItemList[$menuItem]) && $this->menuItemList[$menuItem]->menuPosition == 'footer') {
			// ignore footer items
			return;
		}
		
		parent::setActiveMenuItem($menuItem);
	}
}
