<?php
namespace wcf\system\cache\builder;
use wcf\data\page\menu\item\PageMenuItem;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Caches the page menu items.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class PageMenuCacheBuilder implements ICacheBuilder {
	/**
	 * @see	wcf\system\cache\ICacheBuilder::getData()
	 */
	public function getData(array $cacheResource) { 
		$data = array();
		
		// get all menu items and filter menu items with low priority
		$sql = "SELECT	menuItem, menuItemID 
			FROM	wcf".WCF_N."_page_menu_item";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		$itemIDs = array();
		while ($row = $statement->fetchArray()) {
			$itemIDs[$row['menuItem']] = $row['menuItemID'];
		}
		
		if (!empty($itemIDs)) {
			// get needed menu items and build item tree
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("menu_item.menuItemID IN (?)", array($itemIDs));
			$conditions->add("menu_item.isDisabled = ?", array(0));
			
			$sql = "SELECT		menuItemID, menuItem, parentMenuItem, menuItemLink,
						permissions, options, menuPosition, className
				FROM		wcf".WCF_N."_page_menu_item menu_item
				".$conditions."
				ORDER BY	showOrder ASC";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());
			while ($row = $statement->fetchArray()) {
				$data[($row['parentMenuItem'] ? $row['parentMenuItem'] : $row['menuPosition'])][] = new PageMenuItem(null, $row);
			}
		}
		
		return $data;
	}
}
