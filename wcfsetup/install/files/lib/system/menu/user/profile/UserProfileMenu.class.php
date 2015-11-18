<?php
namespace wcf\system\menu\user\profile;
use wcf\data\user\profile\menu\item\UserProfileMenuItem;
use wcf\system\cache\builder\UserProfileMenuCacheBuilder;
use wcf\system\event\EventHandler;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Builds the user profile menu.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.menu.user.profile
 * @category	Community Framework
 */
class UserProfileMenu extends SingletonFactory {
	/**
	 * active menu item
	 * @var	UserProfileMenuItem
	 */
	public $activeMenuItem = null;
	
	/**
	 * @var EventHandler
	 */
	protected $eventHandler;
	
	/**
	 * list of all menu items
	 * @var	UserProfileMenuItem[]
	 */
	public $menuItems = null;
	
	/**
	 * @var UserProfileMenuCacheBuilder
	 */
	protected $userProfileMenuCacheBuilder;
	
	/**
	 * UserProfileMenu constructor.
	 * 
	 * @param       EventHandler                    $eventHandler
	 * @param       UserProfileMenuCacheBuilder     $userProfileMenuCacheBuilder
	 */
	public function __construct(EventHandler $eventHandler, UserProfileMenuCacheBuilder $userProfileMenuCacheBuilder) {
		$this->eventHandler = $eventHandler;
		$this->userProfileMenuCacheBuilder = $userProfileMenuCacheBuilder;
		
		parent::__construct();
	}
	
	/**
	 * @see	\wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		// get menu items from cache
		$this->loadCache();
		
		// check menu items
		$this->checkMenuItems();
		
		// call init event
		$this->eventHandler->fireAction($this, 'init');
	}
	
	/**
	 * Loads cached menu items.
	 */
	protected function loadCache() {
		// call loadCache event
		$this->eventHandler->fireAction($this, 'loadCache');
		
		$this->menuItems = $this->userProfileMenuCacheBuilder->getData();
	}
	
	/**
	 * Checks the options and permissions of the menu items.
	 */
	protected function checkMenuItems() {
		foreach ($this->menuItems as $key => $item) {
			if (!$this->checkMenuItem($item)) {
				// remove this item
				unset($this->menuItems[$key]);
			}
		}
	}
	
	/**
	 * Checks the options and permissions of given menu item.
	 * 
	 * @param	UserProfileMenuItem     $item
	 * @return	boolean
	 */
	protected function checkMenuItem(UserProfileMenuItem $item) {
		return $item->validateOptions() && $item->validatePermissions();
	}
	
	/**
	 * Returns the list of menu items.
	 * 
	 * @return	UserProfileMenuItem[]
	 */
	public function getMenuItems() {
		return $this->menuItems;
	}
	
	/**
	 * Sets active menu item.
	 * 
	 * @param	string		$menuItem
	 * @return	boolean
	 */
	public function setActiveMenuItem($menuItem) {
		foreach ($this->menuItems as $item) {
			if ($item->menuItem == $menuItem) {
				$this->activeMenuItem = $item;
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Returns the first menu item.
	 * 
	 * @return	UserProfileMenuItem
	 */
	public function getActiveMenuItem() {
		if (empty($this->menuItems)) {
			return null;
		}
		
		if ($this->activeMenuItem === null) {
			reset($this->menuItems);
			$this->activeMenuItem = current($this->menuItems);
		}
		
		return $this->activeMenuItem;
	}
	
	/**
	 * Returns a specific menu item.
	 * 
	 * @return	UserProfileMenuItem
	 */
	public function getMenuItem($menuItem) {
		foreach ($this->menuItems as $item) {
			if ($item->menuItem == $menuItem) {
				return $item;
			}
		}
		
		return null;
	}
}
