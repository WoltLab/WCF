<?php
namespace wcf\system\menu\page;
use wcf\data\page\menu\item\PageMenuItem;
use wcf\system\menu\TreeMenu;
use wcf\system\menu\TreeMenuItem;
use wcf\system\cache\CacheHandler;

/**
 * Builds the page menu.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.menu.page
 * @category 	Community Framework
 */
class PageMenu extends TreeMenu {
	/**
	 * @see TreeMenu::loadCache()
	 */
	protected function loadCache() {
		parent::loadCache();
		
		// get cache
		CacheHandler::getInstance()->addResource('pageMenu-'.PACKAGE_ID, WCF_DIR.'cache/cache.pageMenu-'.PACKAGE_ID.'.php', 'wcf\system\cache\CacheBuilderPageMenu');
		$this->menuItems = CacheHandler::getInstance()->get('pageMenu-'.PACKAGE_ID);
	}
	
	/**
	 * @see TreeMenu::checkMenuItem()
	 */
	protected function checkMenuItem(TreeMenuItem $item) {
		if (!parent::checkMenuItem($item)) return false;
		
		return $item->getProvider()->isVisible();
	}
}
