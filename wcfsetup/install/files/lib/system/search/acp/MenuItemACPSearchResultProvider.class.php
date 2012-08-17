<?php
namespace wcf\system\search\acp;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\package\PackageDependencyHandler;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * ACP search provider for menu items.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.search.acp
 * @category 	Community Framework
 */
class MenuItemACPSearchResultProvider implements IACPSearchResultProvider {
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
		$conditions->add("menuItemLink <> ''");
		
		$sql = "SELECT	menuItem, menuItemLink, permissions, options
			FROM	wcf".WCF_N."_acp_menu_item
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql); // don't use a limit here
		$statement->execute($conditions->getParameters());
		
		$count = 0;
		while ($row = $statement->fetchArray()) {
			if ($count == $limit) {
				break;
			}
			
			if ($this->checkMenuItem($row)) {
				$results[] = new ACPSearchResult($languageItems[$row['menuItem']], $row['menuItemLink'] . SID_ARG_1ST);
				$count++;
			}
		}
		
		return $results;
	}
	
	/**
	 * Validates options and permissions for a given menu item.
	 * 
	 * @param	array		$row
	 * @return	boolean
	 */
	protected function checkMenuItem(array $row) {
		// check the options of this item
		$hasEnabledOption = true;
		if (!empty($row['options'])) {
			$hasEnabledOption = false;
			$options = explode(',', strtoupper($row['options']));
			foreach ($options as $option) {
				if (defined($option) && constant($option)) {
					$hasEnabledOption = true;
					break;
				}
			}
		}
		if (!$hasEnabledOption) return false;
		
		// check the permission of this item for the active user
		$hasPermission = true;
		if (!empty($row['permissions'])) {
			$hasPermission = false;
			$permissions = explode(',', $row['permissions']);
			foreach ($permissions as $permission) {
				if (WCF::getSession()->getPermission($permission)) {
					$hasPermission = true;
					break;
				}
			}
		}
		if (!$hasPermission) return false;
		
		return true;
	}
}
