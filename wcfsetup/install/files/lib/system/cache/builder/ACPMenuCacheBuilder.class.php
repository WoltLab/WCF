<?php
namespace wcf\system\cache\builder;
use wcf\data\acp\menu\item\ACPMenuItem;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Caches the acp menu items tree.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class ACPMenuCacheBuilder implements ICacheBuilder {
	/**
	 * option category structure
	 * @var	array
	 */
	protected $optionCategoryStructure = array();
	
	/**
	 * @see	wcf\system\cache\ICacheBuilder::getData()
	 */
	public function getData(array $cacheResource) { 
		$data = array();
		
		// get all menu items and filter menu items with low priority
		$sql = "SELECT		menuItem, menuItemID
			FROM		wcf".WCF_N."_acp_menu_item menu_item";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		$itemIDs = array();
		while ($row = $statement->fetchArray()) {
			$itemIDs[$row['menuItem']] = $row['menuItemID'];
		}
		
		if (!empty($itemIDs)) {
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
		$optionCategories = $this->getTopOptionCategories();
		if (!empty($optionCategories)) {
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
					'menuItem' => 'wcf.acp.option.category.'.$row['categoryName'],
					'parentMenuItem' => 'wcf.acp.menu.link.option.category',
					'menuItemLink' => 'index.php/Option/'.$row['categoryID'].'/',
					'packageDir' => '',
					'permissions' => $row['permissions'],
					'options' => $row['options']
				));
			}
		}
		
		return $data;
	}
	
	protected function getTopOptionCategories() {
		// get all option categories
		$sql = "SELECT		categoryName, categoryID 
			FROM		wcf".WCF_N."_option_category";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		$optionCategories = array();
		while ($row = $statement->fetchArray()) {
			$optionCategories[$row['categoryName']] = $row['categoryID'];
		}
		
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("categoryID IN (?)", array($optionCategories));
		$sql = "SELECT 		categoryID, parentCategoryName, categoryName,
					(
						SELECT COUNT(*) FROM wcf".WCF_N."_option WHERE categoryName = category.categoryName
					) AS count
			FROM		wcf".WCF_N."_option_category category
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
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
