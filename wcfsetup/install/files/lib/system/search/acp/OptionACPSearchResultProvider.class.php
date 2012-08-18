<?php
namespace wcf\system\search\acp;
use wcf\data\option\Option;
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
class OptionACPSearchResultProvider extends AbstractCategorizedACPSearchResultProvider implements IACPSearchResultProvider {
	/**
	 * @see	wcf\system\search\acp\AbstractCategorizedACPSearchResultProvider::$listClassName
	 */
	protected $listClassName = 'wcf\data\option\category\OptionCategoryList';
	
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
		
		$sql = "SELECT	optionName, categoryName, options, permissions
			FROM	wcf".WCF_N."_option
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		
		while ($row = $statement->fetchArray()) {
			// category is not accessible
			if (!$this->isValid($row['categoryName'])) {
				continue;
			}
			
			// option is not accessible
			$option = new Option(null, $row);
			if (!$this->validate($option)) {
				continue;
			}
			
			$link = LinkHandler::getInstance()->getLink('Option', array('id' => $this->getCategoryID($row['categoryName'])), 'optionName='.$row['optionName'].'#'.$this->getCategoryName($row['categoryName']));
			$results[] = new ACPSearchResult($languageItems[$row['optionName']], $link);
		}
		
		return $results;
	}
}
