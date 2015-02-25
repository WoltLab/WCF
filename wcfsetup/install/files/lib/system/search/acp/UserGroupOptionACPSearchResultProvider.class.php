<?php
namespace wcf\system\search\acp;
use wcf\system\cache\builder\UserGroupOptionCacheBuilder;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * ACP search provider implementation for user group options.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.search.acp
 * @category	Community Framework
 */
class UserGroupOptionACPSearchResultProvider extends AbstractCategorizedACPSearchResultProvider implements IACPSearchResultProvider {
	/**
	 * @see	\wcf\system\search\acp\AbstractCategorizedACPSearchResultProvider::$listClassName
	 */
	protected $listClassName = 'wcf\data\user\group\option\category\UserGroupOptionCategoryList';
	
	/**
	 * @see	\wcf\system\search\acp\IACPSearchResultProvider::search()
	 */
	public function search($query) {
		$results = array();
		
		// search by language item
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("languageID = ?", array(WCF::getLanguage()->languageID));
		$conditions->add("languageItem LIKE ?", array('wcf.acp.group.option.%'));
		$conditions->add("languageItemValue LIKE ?", array('%'.$query.'%'));
		
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
		
		$optionCategories = UserGroupOptionCacheBuilder::getInstance()->getData(array(), 'categories');
		
		while ($userGroupOption = $statement->fetchObject('wcf\data\user\group\option\UserGroupOption')) {
			// category is not accessible
			if (!$this->isValid($userGroupOption->categoryName)) {
				continue;
			}
			
			// option is not accessible
			if (!$this->validate($userGroupOption)) {
				continue;
			}
			
			$link = LinkHandler::getInstance()->getLink('UserGroupOption', array('id' => $userGroupOption->optionID));
			$categoryName = $userGroupOption->categoryName;
			$parentCategories = array();
			while (isset($optionCategories[$categoryName])) {
				array_unshift($parentCategories, 'wcf.acp.group.option.category.'.$optionCategories[$categoryName]->categoryName);
				
				$categoryName = $optionCategories[$categoryName]->parentCategoryName;
			}
			
			$results[] = new ACPSearchResult($languageItems[$userGroupOption->optionName], $link, WCF::getLanguage()->getDynamicVariable('wcf.acp.search.result.subtitle', array(
				'pieces' => $parentCategories
			)));
		}
		
		return $results;
	}
}
