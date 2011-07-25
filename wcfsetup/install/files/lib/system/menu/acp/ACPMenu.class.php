<?php
namespace wcf\system\menu\acp;
use wcf\system\menu\TreeMenu;
use wcf\system\cache\CacheHandler;

/**
 * Builds the acp menu.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.menu.acp
 * @category 	Community Framework
 */
class ACPMenu extends TreeMenu {
	/**
	 * @see TreeMenu::loadCache()
	 */
	protected function loadCache() {
		parent::loadCache();
		
		if (PACKAGE_ID == 0) {
			return;
		}
		
		CacheHandler::getInstance()->addResource('menu-'.PACKAGE_ID, WCF_DIR.'cache/cache.menu-'.PACKAGE_ID.'.php', 'wcf\system\cache\builder\CacheBuilderACPMenu');
		$this->menuItems = CacheHandler::getInstance()->get('menu-'.PACKAGE_ID);
	}
}
