<?php
namespace wcf\system\menu\user;
use wcf\data\ProcessibleDatabaseObject;
use wcf\system\cache\builder\UserMenuCacheBuilder;
use wcf\system\event\EventHandler;
use wcf\system\menu\ITreeMenuItem;
use wcf\system\menu\page\IPageMenuItemProvider;
use wcf\system\menu\TreeMenu;

/**
 * Builds the user menu.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.menu.user
 * @category	Community Framework
 */
class UserMenu extends TreeMenu {
	/**
	 * @var UserMenuCacheBuilder
	 */
	protected $userMenuCacheBuilder;
	
	/**
	 * UserMenu constructor.
	 * 
	 * @param       EventHandler            $eventHandler
	 * @param       UserMenuCacheBuilder    $userMenuCacheBuilder
	 */
	public function __construct(EventHandler $eventHandler, UserMenuCacheBuilder $userMenuCacheBuilder) {
		$this->userMenuCacheBuilder = $userMenuCacheBuilder;
		
		parent::__construct($eventHandler);
	}
	
	/**
	 * @see	TreeMenu::loadCache()
	 */
	protected function loadCache() {
		parent::loadCache();
		
		$this->menuItems = $this->userMenuCacheBuilder->getData();
	}
	
	/**
	 * @see	TreeMenu::checkMenuItem()
	 */
	protected function checkMenuItem(ITreeMenuItem $item) {
		if (!parent::checkMenuItem($item)) return false;
		
		if ($item instanceof ProcessibleDatabaseObject && $item->getProcessor() instanceof IPageMenuItemProvider) {
			return $item->getProcessor()->isVisible();
		}
		
		return true;
	}
}
