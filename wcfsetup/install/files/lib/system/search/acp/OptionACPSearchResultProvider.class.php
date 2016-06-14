<?php
namespace wcf\system\search\acp;
use wcf\data\option\category\OptionCategoryList;
use wcf\data\option\Option;
use wcf\system\cache\builder\OptionCacheBuilder;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * ACP search provider implementation for options.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Search\Acp
 */
class OptionACPSearchResultProvider extends AbstractCategorizedACPSearchResultProvider implements IACPSearchResultProvider {
	/**
	 * @inheritDoc
	 */
	protected $listClassName = OptionCategoryList::class;
	
	/**
	 * @inheritDoc
	 */
	public function search($query) {
		$results = [];
		
		// search by language item
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("languageID = ?", [WCF::getLanguage()->languageID]);
		$conditions->add("languageItem LIKE ?", ['wcf.acp.option.%']);
		$conditions->add("languageItemValue LIKE ?", ['%'.$query.'%']);
		
		$sql = "SELECT		languageItem, languageItemValue
			FROM		wcf".WCF_N."_language_item
			".$conditions."
			ORDER BY	languageItemValue ASC";
		$statement = WCF::getDB()->prepareStatement($sql); // don't use a limit here
		$statement->execute($conditions->getParameters());
		$languageItems = [];
		$optionNames = [];
		while ($row = $statement->fetchArray()) {
			$optionName = preg_replace('~^([a-z]+)\.acp\.option\.~', '', $row['languageItem']);
			
			$languageItems[$optionName] = $row['languageItemValue'];
			$optionNames[] = $optionName;
		}
		
		if (empty($optionNames)) {
			return [];
		}
		
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("optionName IN (?)", [$optionNames]);
		
		$sql = "SELECT	optionName, categoryName, options, permissions, hidden
			FROM	wcf".WCF_N."_option
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql); // don't use a limit here
		$statement->execute($conditions->getParameters());
		
		$optionCategories = OptionCacheBuilder::getInstance()->getData([], 'categories');
		
		while ($option = $statement->fetchObject(Option::class)) {
			// category is not accessible
			if (!$this->isValid($option->categoryName)) {
				continue;
			}
			
			// option is not accessible
			if (!$this->validate($option) || $option->hidden) {
				continue;
			}
			
			$link = LinkHandler::getInstance()->getLink('Option', ['id' => $this->getCategoryID($this->getTopCategory($option->categoryName)->parentCategoryName)], 'optionName='.$option->optionName.'#'.$this->getCategoryName($option->categoryName));
			$categoryName = $option->categoryName;
			$parentCategories = [];
			while (isset($optionCategories[$categoryName])) {
				array_unshift($parentCategories, 'wcf.acp.option.category.'.$optionCategories[$categoryName]->categoryName);
				
				$categoryName = $optionCategories[$categoryName]->parentCategoryName;
			}
			
			$results[] = new ACPSearchResult($languageItems[$option->optionName], $link, WCF::getLanguage()->getDynamicVariable('wcf.acp.search.result.subtitle', [
				'pieces' => $parentCategories
			]));
		}
		
		return $results;
	}
}
