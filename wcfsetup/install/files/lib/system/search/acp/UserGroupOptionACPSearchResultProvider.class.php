<?php
namespace wcf\system\search\acp;
use wcf\data\user\group\option\UserGroupOption;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\package\PackageDependencyHandler;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * ACP search provider for user group options.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.search.acp
 * @category 	Community Framework
 */
class UserGroupOptionACPSearchResultProvider extends AbstractCategorizedACPSearchResultProvider implements IACPSearchResultProvider {
	/**
	 * @see	wcf\system\search\acp\AbstractCategorizedACPSearchResultProvider::$listClassName
	 */
	protected $listClassName = 'wcf\data\user\group\option\category\UserGroupOptionCategoryList';
	
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
		foreach (ACPSearchHandler::getInstance()->getAbbreviations('.acp.group.option.%') as $abbreviation) {
			if (!empty($languageItemsConditions)) $languageItemsConditions .= " OR ";
			$languageItemsConditions .= "languageItem LIKE ?";
			$languageItemsParameters[] = $abbreviation;
		}
		$conditions->add("(".$languageItemsConditions.")", $languageItemsParameters);
		
		$sql = "SELECT		languageItem, languageItemValue
			FROM		wcf".WCF_N."_language_item
			".$conditions."
			ORDER BY	languageItemValue ASC";
		$statement = WCF::getDB()->prepareStatement($sql); // don't use a limit here
		$statement->execute($conditions->getParameters());
		$languageItems = array();
		while ($row = $statement->fetchArray()) {
			// ignore descriptions
			if (substr($row['languageItem'], -12) == '.description') {
				continue;
			}
			
			$itemName = preg_replace('~^([a-z]+)\.acp\.group\.option\.~', '', $row['languageItem']);
			$languageItems[$itemName] = $row['languageItemValue'];
		}
		
		if (empty($languageItems)) {
			return array();
		}
		
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("optionName IN (?)", array(array_keys($languageItems)));
		
		$sql = "SELECT	optionID, optionName, categoryName, permissions, options
			FROM	wcf".WCF_N."_user_group_option
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql); // don't use a limit here
		$statement->execute($conditions->getParameters());
		
		$count = 0;
		while ($row = $statement->fetchArray()) {
			if ($count == $limit) {
				break;
			}
			
			// category is not accessible
			if (!$this->isValid($row['categoryName'])) {
				continue;
			}
			
			// option is not accessible
			$userGroupOption = new UserGroupOption(null, $row);
			if (!$this->validate($userGroupOption)) {
				continue;
			}
			
			$link = LinkHandler::getInstance()->getLink('UserGroupOption', array('id' => $row['optionID']));
			$results[] = new ACPSearchResult($languageItems[$row['optionName']], $link);
			$count++;
		}
		
		return $results;
	}
}
