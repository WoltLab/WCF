<?php
namespace wcf\system\cache\builder;
use wcf\data\option\category\OptionCategory;
use wcf\data\option\Option;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Caches the options and option categories
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category 	Community Framework
 */
class OptionCacheBuilder implements ICacheBuilder {
	/**
	 * @see wcf\system\cache\ICacheBuilder::getData()
	 */
	public function getData(array $cacheResource) {
		$information = explode('-', $cacheResource['cache']);
		if (count($information) == 3) {
			$type = $information[0].'_';
			$packageID = $information[2];
		}
		else {
			$type = '';
			$packageID = $information[1];
		}
		 
		$data = array(
			'categories' => array(),
			'options' => array(),
			'categoryStructure' => array(),
			'optionToCategories' => array()
		);

		// option categories
		// get all option categories and sort categories by priority
		$sql = "SELECT		categoryName, categoryID 
			FROM		wcf".WCF_N."_".$type."option_category option_category
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
		
		if (count($optionCategories) > 0) {
			// get needed option categories
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("categoryID IN (?)", array($optionCategories));
			
			$sql = "SELECT		option_category.*, package.packageDir
				FROM		wcf".WCF_N."_".$type."option_category option_category
				LEFT JOIN	wcf".WCF_N."_package package
				ON		(package.packageID = option_category.packageID)
				".$conditions."
				ORDER BY	showOrder ASC";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());
			while ($row = $statement->fetchArray()) {
				$data['categories'][$row['categoryName']] = new OptionCategory(null, $row);
				if (!isset($data['categoryStructure'][$row['parentCategoryName']])) {
					$data['categoryStructure'][$row['parentCategoryName']] = array();
				}
				
				$data['categoryStructure'][$row['parentCategoryName']][] = $row['categoryName'];
			}
		}
		
		// options
		// get all options and sort options by priority
		$optionIDs = array();
		$sql = "SELECT		optionName, optionID 
			FROM		wcf".WCF_N."_".$type."option option_table
			LEFT JOIN	wcf".WCF_N."_package_dependency package_dependency
			ON		(package_dependency.dependency = option_table.packageID)
			WHERE 		package_dependency.packageID = ?
			ORDER BY	package_dependency.priority ASC";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($packageID));
		while ($row = $statement->fetchArray()) {
			$optionIDs[$row['optionName']] = $row['optionID'];
		}
		
		if (count($optionIDs) > 0) {
			// get needed options
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("optionID IN (?)", array($optionIDs));
			
			$sql = "SELECT		*
				FROM		wcf".WCF_N."_".$type."option
				".$conditions."
				ORDER BY	showOrder ASC";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());
			while ($row = $statement->fetchArray()) {
				$data['options'][$row['optionName']] = new Option(null, $row);
				if (!isset($data['optionToCategories'][$row['categoryName']])) {
					$data['optionToCategories'][$row['categoryName']] = array();
				}
				
				$data['optionToCategories'][$row['categoryName']][] = $row['optionName'];
			}
		}
		
		return $data;
	}
}
