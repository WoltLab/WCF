<?php
namespace wcf\system\search\acp;
use wcf\system\cache\builder\OptionCacheBuilder;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * ACP search provider implementation for options.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.search.acp
 * @category	Community Framework
 */
class OptionACPSearchResultProvider extends AbstractCategorizedACPSearchResultProvider implements IACPSearchResultProvider {
	/**
	 * @see	\wcf\system\search\acp\AbstractCategorizedACPSearchResultProvider::$listClassName
	 */
	protected $listClassName = 'wcf\data\option\category\OptionCategoryList';
	
	/**
	 * @see	\wcf\system\search\acp\IACPSearchResultProvider::search()
	 */
	public function search($query) {
		$results = array();
		
		// search by language item
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("languageID = ?", array(WCF::getLanguage()->languageID));
		$conditions->add("languageItem LIKE ?", array('wcf.acp.option.%'));
		$conditions->add("languageItemValue LIKE ?", array('%'.$query.'%'));
		
		$sql = "SELECT		languageItem, languageItemValue
			FROM		wcf".WCF_N."_language_item
			".$conditions."
			ORDER BY	languageItemValue ASC";
		$statement = WCF::getDB()->prepareStatement($sql); // don't use a limit here
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
		$statement = WCF::getDB()->prepareStatement($sql); // don't use a limit here
		$statement->execute($conditions->getParameters());
		
		$optionCategories = OptionCacheBuilder::getInstance()->getData(array(), 'categories');
		
		while ($option = $statement->fetchObject('wcf\data\option\Option')) {
			// category is not accessible
			if (!$this->isValid($option->categoryName)) {
				continue;
			}
			
			// option is not accessible
			if (!$this->validate($option)) {
				continue;
			}
			
			$link = LinkHandler::getInstance()->getLink('Option', array('id' => $this->getCategoryID($this->getTopCategory($option->categoryName)->parentCategoryName)), 'optionName='.$option->optionName.'#'.$this->getCategoryName($option->categoryName));
			$categoryName = $option->categoryName;
			$parentCategories = array();
			while (isset($optionCategories[$categoryName])) {
				array_unshift($parentCategories, 'wcf.acp.option.category.'.$optionCategories[$categoryName]->categoryName);
				
				$categoryName = $optionCategories[$categoryName]->parentCategoryName;
			}
			
			$results[] = new ACPSearchResult($languageItems[$option->optionName], $link, WCF::getLanguage()->getDynamicVariable('wcf.acp.search.result.subtitle', array(
				'pieces' => $parentCategories
			)));
		}
		
		return $results;
	}
}
