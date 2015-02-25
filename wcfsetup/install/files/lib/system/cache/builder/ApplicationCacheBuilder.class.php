<?php
namespace wcf\system\cache\builder;
use wcf\data\application\ApplicationList;
use wcf\data\package\Package;
use wcf\data\package\PackageList;

/**
 * Caches applications.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class ApplicationCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @see	\wcf\system\cache\builder\AbstractCacheBuilder::rebuild()
	 */
	public function rebuild(array $parameters) {
		$data = array(
			'abbreviation' => array(),
			'application' => array(),
			'primary' => 0,
			'wcf' => null
		);
		
		// fetch applications
		$applicationList = new ApplicationList();
		$applicationList->readObjects();
		$applications = $applicationList->getObjects();
		
		foreach ($applications as $application) {
			$data['application'][$application->packageID] = $application;
			
			// save primary application's package id
			if ($application->isPrimary) {
				$data['primary'] = $application->packageID;
			}
		}
		
		// fetch abbreviations
		$packageList = new PackageList();
		$packageList->getConditionBuilder()->add('package.isApplication = ?', array(1));
		$packageList->readObjects();
		foreach ($packageList->getObjects() as $package) {
			$data['abbreviation'][Package::getAbbreviation($package->package)] = $package->packageID;
		}
		
		// assign wcf pseudo-application
		if (PACKAGE_ID) {
			$data['wcf'] = $data['application'][1];
			unset($data['application'][1]);
		}
		
		return $data;
	}
}
