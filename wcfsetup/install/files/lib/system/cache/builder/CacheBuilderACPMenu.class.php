<?php
namespace wcf\system\cache\builder;
use wcf\data\acp\menu\item\ACPMenuItem;
use wcf\system\cache\CacheBuilder;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Caches the acp menu items tree.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache
 * @category 	Community Framework
 */
class CacheBuilderACPMenu implements CacheBuilder {
	protected $optionCategoryStructure = array();

	/**
	 * @see CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		list($cache, $packageID) = explode('-', $cacheResource['cache']); 
		$data = array();

		// get all menu items and filter menu items with low priority
		$sql = "SELECT		menuItem, menuItemID
			FROM		wcf".WCF_N."_acp_menu_item menu_item
			LEFT JOIN	wcf".WCF_N."_package_dependency package_dependency
			ON 		(menu_item.packageID = package_dependency.dependency)
			WHERE		package_dependency.packageID = ?
			ORDER BY	package_dependency.priority ASC";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($packageID));
		$itemIDs = array();
		while ($row = $statement->fetchArray()) {
			$itemIDs[$row['menuItem']] = $row['menuItemID'];
		}
		
		if (count($itemIDs) > 0) {
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("menuItemID IN (?)", array($itemIDs));
			
			// get needed menu items and build item tree
			$sql = "SELECT		menu_item.packageID, menuItem, parentMenuItem,
						menuItemLink, permissions, options, packageDir
				FROM		wcf".WCF_N."_acp_menu_item menu_item
				LEFT JOIN	wcf".WCF_N."_package package
				ON		(package.packageID = menu_item.packageID)
				".$conditions."
				ORDER BY	showOrder ASC";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());
			while ($row = $statement->fetchArray()) {
				if (!isset($data[$row['parentMenuItem']])) {
					$data[$row['parentMenuItem']] = array();
				}
				
				$data[$row['parentMenuItem']][] = new ACPMenuItem(null, $row);
			}
		}
		
		// get top option categories
		$optionCategories = $this->getTopOptionCategories($packageID);
		if (count($optionCategories) > 0) {
			if (!isset($data['wcf.acp.menu.link.option.category'])) {
				$data['wcf.acp.menu.link.option.category'] = array();
			}
			
			// get option category data
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("categoryID IN (?)", array($optionCategories));
			
			$sql = "SELECT		*
				FROM		wcf".WCF_N."_option_category
				".$conditions."
				ORDER BY	showOrder ASC";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());
			while ($row = $statement->fetchArray()) {
				$data['wcf.acp.menu.link.option.category'][] = new ACPMenuItem(null, array(
					'packageID' => $packageID,
					'menuItem' => 'wcf.acp.option.category.'.$row['categoryName'],
					'parentMenuItem' => 'wcf.acp.menu.link.option.category',
					'menuItemLink' => 'index.php?form=Option&categoryID='.$row['categoryID'],
					'packageDir' => '',
					'permissions' => $row['permissions'],
					'options' => $row['options']
				));
			}
		}
		
		return $data;
	}
	
	protected function getTopOptionCategories($packageID) {
		// get all option categories and filter categories with low priority
		$sql = "SELECT		categoryName, categoryID 
			FROM		wcf".WCF_N."_option_category option_category
			LEFT JOIN	wcf".WCF_N."_package_dependency package_dependency
			ON		(package_dependency.dependency = option_category.packageID)
			WHERE 		package_dependency.packageID = ?
			ORDER BY	package_dependency.priority ASC";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($packageID));
		$optionCategories = array();
		while ($row = $statement->fetchArray()) {
			$optionCategories[$row['categoryName']] = $row['categoryID'];
		}
		
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("categoryID IN (?)", array($optionCategories));
		$statementParameters = $conditions->getParameters();
		array_unshift($statementParameters, $packageID);
		
		$sql = "SELECT 		categoryID, parentCategoryName, categoryName,
					(
						SELECT COUNT(*) FROM wcf".WCF_N."_option WHERE categoryName = category.categoryName AND packageID IN (
							SELECT dependency FROM wcf".WCF_N."_package_dependency WHERE packageID = ?
						)
					) AS count
			FROM		wcf".WCF_N."_option_category category
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($statementParameters);
		while ($row = $statement->fetchArray()) {
			if (!isset($this->optionCategoryStructure[$row['parentCategoryName']])) $this->optionCategoryStructure[$row['parentCategoryName']] = array();
			$this->optionCategoryStructure[$row['parentCategoryName']][] = $row;
		}
		
		$topOptionCategories = array();
		foreach ($this->optionCategoryStructure[''] as $optionCategory) {
			$count = $optionCategory['count'] + $this->countOptions($optionCategory['categoryName']);
			if ($count > 0) $topOptionCategories[] = $optionCategory['categoryID'];
		}
		
		return $topOptionCategories;
	}
	
	protected function countOptions($parentCategoryName) {
		if (!isset($this->optionCategoryStructure[$parentCategoryName])) return 0;
		
		$count = 0;
		foreach ($this->optionCategoryStructure[$parentCategoryName] as $optionCategory) {
			$count += $optionCategory['count'] + $this->countOptions($optionCategory['categoryName']);
		}
		
		return $count;
	}
}
