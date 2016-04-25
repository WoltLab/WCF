<?php
namespace wcf\system\menu\user;
use wcf\system\cache\builder\UserMenuCacheBuilder;
use wcf\system\menu\ITreeMenuItem;
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
		if (!parent::checkMenuItem($item)) return false;
		
		return $item->getProcessor()->isVisible();
	}
}
