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
	 * @inheritDoc
	 */
	public function rebuild(array $parameters) {
		$data = [
			'abbreviation' => [],
			'application' => []
		];
		
		// fetch applications
		$applicationList = new ApplicationList();
		$applicationList->readObjects();
		
		foreach ($applicationList as $application) {
			$data['application'][$application->packageID] = $application;
		}
		
		// fetch abbreviations
		$packageList = new PackageList();
		$packageList->getConditionBuilder()->add('package.isApplication = ?', [1]);
		$packageList->readObjects();
		foreach ($packageList as $package) {
			$data['abbreviation'][Package::getAbbreviation($package->package)] = $package->packageID;
		}
		
		return $data;
	}
}
