<?php
namespace wcf\system\menu\user;
use wcf\data\user\menu\item\UserMenuItem;
use wcf\system\cache\builder\UserMenuCacheBuilder;
use wcf\system\menu\ITreeMenuItem;
use wcf\system\menu\TreeMenu;

/**
 * Builds the user menu.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Menu\User
 */
class UserMenu extends TreeMenu {
	/**
	 * @inheritDoc
	 */
	protected function loadCache() {
		parent::loadCache();
		
		$this->menuItems = UserMenuCacheBuilder::getInstance()->getData();
	}
	
	/**
	 * @inheritDoc
	 */
	protected function checkMenuItem(ITreeMenuItem $item) {
		/** @var UserMenuItem $item */
		
		if (!parent::checkMenuItem($item)) return false;
		
		return $item->getProcessor()->isVisible();
	}
}
