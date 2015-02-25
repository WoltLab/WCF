<?php
namespace wcf\system\cache\builder;
use wcf\data\page\menu\item\PageMenuItemList;

/**
 * Caches the page menu items.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class PageMenuCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @see	\wcf\system\cache\builder\AbstractCacheBuilder::rebuild()
	 */
	public function rebuild(array $parameters) {
		$data = array();
		
		$menuItemList = new PageMenuItemList();
		$menuItemList->getConditionBuilder()->add("(page_menu_item.isDisabled = ? OR page_menu_item.isLandingPage = ?)", array(0, 1));
		$menuItemList->sqlOrderBy = "page_menu_item.showOrder ASC";
		$menuItemList->readObjects();
		
		foreach ($menuItemList as $menuItem) {
			$index = ($menuItem->parentMenuItem) ? $menuItem->parentMenuItem : $menuItem->menuPosition;
			$data[$index][] = $menuItem;
		}
		
		return $data;
	}
}
