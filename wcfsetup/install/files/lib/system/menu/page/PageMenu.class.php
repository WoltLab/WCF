<?php
namespace wcf\system\menu\page;
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
	 * landing page menu item
	 * @var	\wcf\data\page\menu\item\PageMenuItem
	 */
	protected $landingPage = null;
	
	/**
	 * @see	\wcf\system\SingletonFactory::init()
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
		
		foreach ($this->menuItems as $menuItems) {
			foreach ($menuItems as $menuItem) {
				if ($menuItem->isLandingPage) {
					$this->landingPage = $menuItem;
					break 2;
				}
			}
		}
		
		if ($this->landingPage === null) {
			throw new SystemException("Missing landing page");
		}
		
		$this->setActiveMenuItem($this->landingPage->menuItem);
	}
	
	/**
	 * Returns landing page menu item.
	 * 
	 * @return	\wcf\data\page\menu\item\PageMenuItem
	 */
	public function getLandingPage() {
		return $this->landingPage;
	}
	
	/**
	 * @see	\wcf\system\menu\TreeMenu::loadCache()
	 */
	protected function loadCache() {
		parent::loadCache();
		
		// get cache
		$this->menuItems = PageMenuCacheBuilder::getInstance()->getData();
	}
	
	/**
	 * @see	\wcf\system\menu\TreeMenu::checkMenuItem()
	 */
	protected function checkMenuItem(ITreeMenuItem $item) {
		// landing page must always be accessible
		if ($item->isLandingPage) {
			return true;
		}
		
		if (!parent::checkMenuItem($item)) return false;
		
		return $item->getProcessor()->isVisible();
	}
	
	/**
	 * @see	\wcf\system\menu\TreeMenu::setActiveMenuItem()
	 */
	public function setActiveMenuItem($menuItem) {
		if (isset($this->menuItemList[$menuItem]) && $this->menuItemList[$menuItem]->menuPosition == 'footer') {
			// ignore footer items
			return;
		}
		
		parent::setActiveMenuItem($menuItem);
	}
}
