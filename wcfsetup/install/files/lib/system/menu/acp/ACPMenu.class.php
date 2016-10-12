<?php
namespace wcf\system\menu\acp;
use wcf\system\cache\builder\ACPMenuCacheBuilder;
use wcf\system\menu\TreeMenu;

/**
 * Builds the acp menu.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Menu\Acp
 */
class ACPMenu extends TreeMenu {
	/**
	 * @inheritDoc
	 */
	protected function loadCache() {
		parent::loadCache();
		
		if (PACKAGE_ID == 0) {
			return;
		}
		
		$this->menuItems = ACPMenuCacheBuilder::getInstance()->getData();
	}
}
