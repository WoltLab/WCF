<?php
namespace wcf\system\menu\page;
use wcf\data\page\menu\item\PageMenuItem;
use wcf\system\menu\TreeMenu;
use wcf\system\menu\ITreeMenuItem;
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
	 * @see	wcf\system\menu\TreeMenu::loadCache()
	 */
	protected function loadCache() {
		parent::loadCache();
		
		// get cache
		$cacheName = 'pageMenu-'.PACKAGE_ID;
		CacheHandler::getInstance()->addResource(
			$cacheName,
			WCF_DIR.'cache/cache.'.$cacheName.'.php',
			'wcf\system\cache\builder\CacheBuilderPageMenu'
		);
		$this->menuItems = CacheHandler::getInstance()->get($cacheName);
	}
	
	/**
	 * @see	wcf\system\menu\TreeMenu::checkMenuItem()
	 */
	protected function checkMenuItem(ITreeMenuItem $item) {
		if (!parent::checkMenuItem($item)) return false;
		
		return $item->getProcessor()->isVisible();
	}
}
