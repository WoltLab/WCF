<?php
namespace wcf\system\search\acp;
use wcf\data\option\category\OptionCategoryList;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\package\PackageDependencyHandler;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * ACP search provider for options.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.search.acp
 * @category 	Community Framework
 */
class OptionACPSearchResultProvider implements IACPSearchResultProvider {
	/**
	 * list of option categories
	 * @var	array<wcf\data\option\category\OptionCategory>
	 */
	protected $optionCategories = array();
	
	/**
	 * list of level 1 or 2 categories
	 * @var	array<wcf\data\option\category\OptionCategory>
	 */
	protected $topCategories = array();
	
	/**
	 * @see	wcf\system\search\acp\IACPSearchResultProvider::search()
	 */
	public function search($query, $limit = 5) {
		$results = array();
		
		// search by language item
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("languageID = ?", array(WCF::getLanguage()->languageID));
		$conditions->add("languageItemValue LIKE ?", array($query.'%'));
		$conditions->add("packageID IN (?)", array(PackageDependencyHandler::getInstance()->getDependencies()));
		
		// filter by language item
		$languageItemsConditions = '';
		$languageItemsParameters = array();
		foreach (ACPSearchHandler::getInstance()->getAbbreviations('.acp.option.%') as $abbreviation) {
			if (!empty($languageItemsConditions)) $languageItemsConditions .= " OR ";
			$languageItemsConditions .= "languageItem LIKE ?";
			$languageItemsParameters[] = $abbreviation;
		}
		$conditions->add("(".$languageItemsConditions.")", $languageItemsParameters);
		
		$sql = "SELECT		languageItem, languageItemValue
			FROM		wcf".WCF_N."_language_item
			".$conditions."
			ORDER BY	languageItemValue ASC";
		$statement = WCF::getDB()->prepareStatement($sql, $limit);
		$statement->execute($conditions->getParameters());
		$languageItems = array();
		$optionNames = array();
		while ($row = $statement->fetchArray()) {
			$optionName = preg_replace('~^([a-z]+)\.acp\.option\.~', '', $row['languageItem']);
			
			$languageItems[$optionName] = $row['languageItemValue'];
			$optionNames[] = $optionName;
		}
		
		if (empty($optionNames)) {
			return array();
		}
		
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("optionName IN (?)", array($optionNames));
		
		$sql = "SELECT	optionName, categoryName
			FROM	wcf".WCF_N."_option
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		
		while ($row = $statement->fetchArray()) {
			$link = LinkHandler::getInstance()->getLink('Option', array('id' => $this->getCategoryID($row['categoryName'])), 'optionName='.$row['optionName'].'#'.$this->getCategoryName($row['categoryName']));
			$results[] = new ACPSearchResult($languageItems[$row['optionName']], $link);
		}
		
		return $results;
	}
	
	/**
	 * Returns the primary category id for given category name.
	 * 
	 * @param	string		$categoryName
	 * @return	integer
	 */
	protected function getCategoryID($categoryName) {
		// get option categories
		$this->loadCategories();
		
		if (!isset($this->optionCategories[$categoryName])) {
			throw new SystemException("Option category '".$categoryName."' is invalid");
		}
		
		// use the category id of parent category
		if ($this->optionCategories[$categoryName]->parentCategoryName != '') {
			return $this->getCategoryID($this->optionCategories[$categoryName]->parentCategoryName);
		}
		
		return $this->optionCategories[$categoryName]->categoryID;
	}
	
	/**
	 * Returns a level 1 or 2 category name.
	 * 
	 * @param	string		$categoryName
	 * @return	string
	 */
	protected function getCategoryName($categoryName) {
		// get option categories
		$this->loadCategories();
		
		// load level 1
		if (empty($this->topCategories)) {
			foreach ($this->optionCategories as $category) {
				if ($category->parentCategoryName == '') {
					$this->topCategories[$category->categoryName] = $category;
				}
			}
			
			// load level 2
			$secondLevelCategories = array();
			foreach ($this->optionCategories as $category) {
				if ($category->parentCategoryName != '' && isset($this->topCategories[$category->parentCategoryName])) {
					$secondLevelCategories[$category->categoryName] = $category;
				}
			}
			
			$this->topCategories = array_merge($this->topCategories, $secondLevelCategories);
		}
		
		if (!isset($this->optionCategories[$categoryName])) {
			throw new SystemException("Option category '".$categoryName."' is invalid");
		}
		
		if (isset($this->topCategories[$categoryName])) {
			return $categoryName;
		}
		
		return $this->getCategoryName($this->optionCategories[$categoryName]->parentCategoryName);
	}
	
	/**
	 * Loads option categories.
	 */
	protected function loadCategories() {
		if (empty($this->optionCategories)) {
			$categoryList = new OptionCategoryList();
			$categoryList->sqlLimit = 0;
			$categoryList->readObjects();
				
			foreach ($categoryList as $category) {
				$this->optionCategories[$category->categoryName] = $category;
			}
		}
	}
}
