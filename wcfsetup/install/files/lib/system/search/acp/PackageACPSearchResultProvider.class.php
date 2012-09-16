<?php
namespace wcf\system\search\acp;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\package\PackageDependencyHandler;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * ACP search result provider for packages.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.search.acp
 * @category 	Community Framework
 */
class PackageACPSearchResultProvider implements IACPSearchResultProvider {
	/**
	 * @see	wcf\system\search\acp\IACPSearchResultProvider::search()
	 */
	public function search($query) {
		if (!WCF::getSession()->getPermission('admin.system.package.canUpdatePackage') && !WCF::getSession()->getPermission('admin.system.package.canUninstallPackage')) {
			return array();
		}
		
		$results = array();
		
		// search by language item
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("languageID = ?", array(WCF::getLanguage()->languageID));
		$conditions->add("languageItem LIKE ?", array('wcf.acp.package.title.package%'));
		$conditions->add("languageItemValue LIKE ?", array($query.'%'));
		$conditions->add("packageID IN (?)", array(PackageDependencyHandler::getInstance()->getDependencies()));
		
		$sql = "SELECT		languageItem
			FROM		wcf".WCF_N."_language_item
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		
		$packageIDs = array();
		while ($row = $statement->fetchArray()) {
			$packageIDs[] = str_replace('wcf.acp.package.title.package', '', $row['languageItem']);
		}
		
		$conditions = new PreparedStatementConditionBuilder(false);
		if (!empty($packageIDs)) {
			$conditions->add("packageID IN (?)", array($packageIDs));
		}
		
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_package
			WHERE	packageName LIKE ?
				OR package LIKE ?
				".(count($conditions->getParameters()) ? "OR ".$conditions : "");
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array_merge($conditions->getParameters(), array(
			$query.'%',
			$query.'%'
		)));
		
		while ($package = $statement->fetchObject('wcf\data\package\Package')) {
			$results[] = new ACPSearchResult($package->getName(), LinkHandler::getInstance()->getLink('PackageView', array(
				'id' => $package->packageID,
				'title' => $package->getName()
			)));
		}
		
		return $results;
	}
}
