<?php
namespace wcf\system\cache;
use wcf\data\page\menu\item\PageMenuItem;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Caches the page menu items.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache
 * @category 	Community Framework
 */
class CacheBuilderPageMenu implements CacheBuilder {
	/**
	 * @see CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		list($cache, $packageID) = explode('-', $cacheResource['cache']); 
		$data = array();

		// get all menu items and filter menu items with low priority
		$sql = "SELECT		menuItem, menuItemID 
			FROM		wcf".WCF_N."_page_menu_item menu_item
			LEFT JOIN	wcf".WCF_N."_package_dependency package_dependency
			ON		(package_dependency.dependency = menu_item.packageID)
			WHERE 		package_dependency.packageID = ?
			ORDER BY	package_dependency.priority ASC";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($packageID));
		$itemIDs = array();
		while ($row = $statement->fetchArray()) {
			$itemIDs[$row['menuItem']] = $row['menuItemID'];
		}
		
		if (count($itemIDs) > 0) {
			// get needed menu items and build item tree
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("menu_item.menuItemID IN (?)", array($itemIDs));
			$conditions->add("menu_item.isDisabled = ?", array(0));
			
			$sql = "SELECT		menuItemID, menuItem, parentMenuItem, menuItemLink,
						permissions, options, packageDir, menuPosition, className,
						CASE WHEN parentPackageID <> 0 THEN parentPackageID ELSE menu_item.packageID END AS packageID
				FROM		wcf".WCF_N."_page_menu_item menu_item
				LEFT JOIN	wcf".WCF_N."_package package
				ON		(package.packageID = menu_item.packageID)
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
