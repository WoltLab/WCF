<?php
namespace wcf\system\cache;
use wcf\data\application\group\ApplicationGroup;
use wcf\data\application;
use wcf\data\package\Package;
use wcf\data\package\PackageList;
use wcf\system\WCF;

/**
 * Caches applications.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache
 * @category 	Community Framework
 */
class CacheBuilderApplication implements CacheBuilder {
	/**
	 * @see CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		list($cache, $packageID) = explode('-', $cacheResource['cache']);
		$data = array(
			'abbreviation' => array(),
			'application' => array(),
			'group' => null,
			'primary' => 0,
			'wcf' => null
		);
		
		// lookup group id for currently active application
		$sql = "SELECT	groupID
			FROM	wcf".WCF_N."_application
			WHERE	packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($packageID));
		$row = $statement->fetchArray();
		
		// current application is not part of an application group
		if (!$row || ($row['groupID'] == 0) || $row['groupID'] === null) {
			$data['application'] = array($packageID => new application\Application($packageID));
		}
		else {
			// fetch applications
			$applicationList = new application\ApplicationList();
			$applicationList->getConditionBuilder()->add("application.groupID = ?", array($row['groupID']));
			$applicationList->sqlLimit = 0;
			$applicationList->readObjects();
			$applications = $applicationList->getObjects();
			
			foreach ($applications as $application) {
				$data['application'][$application->packageID] = $application;
				
				// save primary application's package id
				if ($application->isPrimary) {
					$data['primary'] = $application->packageID;
				}
			}
			
			// fetch application group
			$data['group'] = new ApplicationGroup($row['groupID']);
		}
		
		// fetch abbreviations
		$packageList = new PackageList();
		$packageList->getConditionBuilder()->add('packageID IN (?)', array(array_keys($data['application'])));
		$packageList->readObjects();
		foreach ($packageList->getObjects() as $package) {
			$data['abbreviation'][Package::getAbbreviation($package->package)] = $package->packageID;
		}
		
		// fetch wcf pseudo-application
		$data['wcf'] = new application\Application(1);
		
		return $data;
	}
}
