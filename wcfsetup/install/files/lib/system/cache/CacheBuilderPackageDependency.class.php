<?php
namespace wcf\system\cache;
use wcf\system\WCF;

/**
 * Caches the dependencies of a package.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache
 * @category 	Community Framework
 */
class CacheBuilderPackageDependency implements CacheBuilder {
	/**
	 * @see CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		list($cache, $packageID) = explode('-', $cacheResource['cache']);
		$data = array(
			'dependency' => array(),
			'resolve' => array()
		);
		
		if ($packageID != 0) {
			// general dependencies for current package id
			$sql = "SELECT	dependency
				FROM	wcf".WCF_N."_package_dependency
				WHERE	packageID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array($packageID));
			while ($row = $statement->fetchArray()) {
				$data['dependency'][] = $row['dependency'];
			}
			
			// resolve package id by package name
			$sql = "SELECT		package.packageID, package.package
				FROM		wcf".WCF_N."_package_dependency package_dependency
				LEFT JOIN	wcf".WCF_N."_package package
				ON		(package.packageID = package_dependency.dependency)
				WHERE		package_dependency.packageID = ?
				ORDER BY	package_dependency.priority ASC";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array($packageID));
			while ($row = $statement->fetchArray()) {
				if (!isset($data['resolve'][$row['package']])) $data['resolve'][$row['package']] = array();
				$data['resolve'][$row['package']][] = $row['packageID'];
			}
			
			foreach ($data['resolve'] as $package => $packageIDArray) {
				if (count($packageIDArray) == 1) {
					$data[$package] = array_shift($packageIDArray);
				}
			}
		}
		
		return $data;
	}
}
