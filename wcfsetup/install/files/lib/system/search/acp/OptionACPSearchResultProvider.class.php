<?php
namespace wcf\system\search\acp;
use wcf\system\database\util\PreparedStatementConditionBuilder;
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
	 * @see	wcf\system\search\acp\IACPSearchResultProvider::search()
	 */
	public function search($query, $limit = 5) {
		$results = array();
		
		// search by language item
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("languageID = ?", array(WCF::getLanguage()->languageID));
		$conditions->add("languageItem LIKE ?", array('wcf.acp.option.'.$query.'%'));
		$conditions->add("packageID IN (?)", array(PackageDependencyHandler::getInstance()->getDependencies()));
		
		$sql = "SELECT		languageItem, languageItemValue
			FROM		wcf".WCF_N."_language_item
			".$conditions."
			ORDER BY	languageItemValue ASC";
		$statement = WCF::getDB()->prepareStatement($sql, $limit);
		$statement->execute($conditions->getParameters());
		$languageItems = array();
		$optionNames = array();
		while ($row = $statement->fetchArray()) {
			$optionName = str_replace('wcf.acp.option.', '', $row['languageItem']);
			
			$languageItems[$optionName] = $row['languageItemValue'];
			$optionNames[] = $optionName;
		}
		
		if (empty($optionNames)) {
			return array();
		}
		
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("option.optionName IN (?)", array($optionNames));
		
		$sql = "SELECT		option.optionName, option.categoryName, option_category.categoryID
			FROM		wcf".WCF_N."_option option
			LEFT JOIN	wcf".WCF_N."_option_category option_category
			ON		(option_category.categoryName = option.categoryName)
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		
		while ($row = $statement->fetchArray()) {
			$link = LinkHandler::getInstance()->getLink('Option', array('id' => $row['categoryID']), '#'.$row['categoryName']);
			$results[] = new ACPSearchResult($languageItems[$row['optionName']], $link);
		}
		
		return $results;
	}
}
