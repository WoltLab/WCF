<?php
namespace wcf\system\search\acp;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * ACP search provider implementation for menu items.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.search.acp
 * @category	Community Framework
 */
class MenuItemACPSearchResultProvider extends AbstractACPSearchResultProvider implements IACPSearchResultProvider {
	/**
	 * @see	\wcf\system\search\acp\IACPSearchResultProvider::search()
	 */
	public function search($query) {
		$results = array();
		
		// search by language item
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("languageID = ?", array(WCF::getLanguage()->languageID));
		$conditions->add("languageItemValue LIKE ?", array($query.'%'));
		
		// filter by language item
		$languageItemsConditions = '';
		$languageItemsParameters = array();
		foreach (ACPSearchHandler::getInstance()->getAbbreviations('.acp.menu.link.%') as $abbreviation) {
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
			$languageItems[$row['languageItem']] = $row['languageItemValue'];
		}
		
		if (empty($languageItems)) {
			return array();
		}
		
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("menuItem IN (?)", array(array_keys($languageItems)));
		$conditions->add("menuItemController <> ''");
		
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_acp_menu_item
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql); // don't use a limit here
		$statement->execute($conditions->getParameters());
		
		while ($menuItem = $statement->fetchObject('wcf\data\acp\menu\item\ACPMenuItem')) {
			if (!$this->validate($menuItem)) {
				continue;
			}
			
			$results[] = new ACPSearchResult($languageItems[$menuItem->menuItem], $menuItem->getLink());
		}
		
		return $results;
	}
}
