<?php
namespace wcf\system\search\acp;
use wcf\system\application\ApplicationHandler;
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
		$conditions->add("languageItem LIKE ?", array('wcf.acp.option.'.$query.'%'));
		$conditions->add("packageID IN (?)", array(PackageDependencyHandler::getInstance()->getDependencies()));
		
		// get available abbrevations
		$packageIDs = array(ApplicationHandler::getInstance()->getActiveApplication()->packageID);
		foreach (ApplicationHandler::getInstance()->getDependentApplications() as $application) {
			$packageIDs[] = $application->packageID;
		}
		
		$searchConditions = array();
		$searchString = '';
		foreach ($packageIDs as $packageID) {
			if (!empty($searchString)) {
				$searchString .= " OR ";
			}
			
			$searchString .= "languageItem LIKE ?";
			$searchConditions[] = ApplicationHandler::getInstance()->getAbbrevation($packageID) . '.acp.menu.link.'.$query.'%';
		}
		$conditions->add($searchString, $searchConditions);
		
		$sql = "SELECT		languageItem, languageItemValue
			FROM		wcf".WCF_N."_language_item
			".$conditions."
			ORDER BY	languageItemValue ASC";
		$statement = WCF::getDB()->prepareStatement($sql, ($limit * 2)); // use double limit here since categories are matched too
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
		
		$sql = "SELECT	menuItem, menuItemLink
			FROM	wcf".WCF_N."_acp_menu_item
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql, $limit);
		$statement->execute($conditions->getParameters());
		
		while ($row = $statement->fetchArray()) {
			$results[] = new ACPSearchResult($languageItems[$row['menuItem']], $row['menuItemLink']);
		}
		
		return $results;
	}
}
