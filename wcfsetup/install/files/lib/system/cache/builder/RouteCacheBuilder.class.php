<?php
namespace wcf\system\cache\builder;
use wcf\data\route\RouteList;
use wcf\data\route\component\RouteComponentList;
use wcf\system\package\PackageDependencyHandler;

/**
 * Caches all routes.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category 	Community Framework
 */
class RouteCacheBuilder implements ICacheBuilder {
	/**
	 * @see wcf\system\cache\ICacheBuilder::getData()
	 */
	public function getData(array $cacheResource) {
		$routeList = new RouteList();
		
		// ignore package id during wcf setup
		if (PACKAGE_ID) {
			$routeList->sqlJoins = "	LEFT JOIN	wcf".WCF_N."_package_dependency package_dependency
							ON		(package_dependency.dependency = route.packageID)";
			$routeList->sqlConditionJoins = $routeList->sqlJoins;
			$routeList->getConditionBuilder()->add("package_dependency.packageID = ?", array(PACKAGE_ID));
			$routeList->sqlOrderBy = "package_dependency.priority ASC";
		}
		
		$routeList->sqlLimit = 0;
		$routeList->readObjectIDs();
		$routeList->readObjects();
		
		$routeComponentList = new RouteComponentList();
		$routeComponentList->getConditionBuilder()->add("routeID IN (?)", array($routeList->getObjectIDs()));
		$routeComponentList->sqlLimit = 0;
		$routeComponentList->readObjects();
		
		return array(
			'components' => $routeComponentList->getObjects(),
			'routes' => $routeList->getObjects()
		);
	}
}
