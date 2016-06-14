<?php
namespace wcf\system\search\acp;
use wcf\data\acp\menu\item\ACPMenuItem;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\menu\acp\ACPMenu;
use wcf\system\WCF;

/**
 * ACP search provider implementation for menu items.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Search\Acp
 */
class MenuItemACPSearchResultProvider extends AbstractACPSearchResultProvider implements IACPSearchResultProvider {
	/**
	 * @inheritDoc
	 */
	public function search($query) {
		$results = [];
		
		// search by language item
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("languageID = ?", [WCF::getLanguage()->languageID]);
		
		// filter by language item
		$languageItemsConditions = '';
		$languageItemsParameters = [];
		foreach (ACPSearchHandler::getInstance()->getAbbreviations('.acp.menu.link.%') as $abbreviation) {
			if (!empty($languageItemsConditions)) $languageItemsConditions .= " OR ";
			$languageItemsConditions .= "languageItem LIKE ?";
			$languageItemsParameters[] = $abbreviation;
		}
		$conditions->add("(".$languageItemsConditions.")", $languageItemsParameters);
		$conditions->add("languageItemValue LIKE ?", ['%'.$query.'%']);
		
		$sql = "SELECT		languageItem, languageItemValue
			FROM		wcf".WCF_N."_language_item
			".$conditions."
			ORDER BY	languageItemValue ASC";
		$statement = WCF::getDB()->prepareStatement($sql); // don't use a limit here
		$statement->execute($conditions->getParameters());
		$languageItems = [];
		while ($row = $statement->fetchArray()) {
			$languageItems[$row['languageItem']] = $row['languageItemValue'];
		}
		
		if (empty($languageItems)) {
			return [];
		}
		
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("menuItem IN (?)", [array_keys($languageItems)]);
		$conditions->add("menuItemController <> ''");
		
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_acp_menu_item
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql); // don't use a limit here
		$statement->execute($conditions->getParameters());
		
		$menuItems = ACPMenu::getInstance()->menuItemList;
		
		/** @var ACPMenuItem $menuItem */
		while ($menuItem = $statement->fetchObject(ACPMenuItem::class)) {
			// only valid menu items exist in TreeMenu::$menuItemList,
			// so no need to call AbstractACPSearchResultProvider::validate()
			if (!isset($menuItems[$menuItem->menuItem])) {
				continue;
			}
			
			$parentMenuItem = $menuItem->parentMenuItem;
			$parentMenuItems = [];
			while ($parentMenuItem && isset($menuItems[$parentMenuItem])) {
				array_unshift($parentMenuItems, $parentMenuItem);
				
				$parentMenuItem = $menuItems[$parentMenuItem]->parentMenuItem;
			}
			$results[] = new ACPSearchResult($languageItems[$menuItem->menuItem], $menuItem->getLink(), WCF::getLanguage()->getDynamicVariable('wcf.acp.search.result.subtitle', [
				'pieces' => $parentMenuItems
			]));
		}
		
		return $results;
	}
}
