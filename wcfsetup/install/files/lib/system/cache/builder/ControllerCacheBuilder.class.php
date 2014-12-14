<?php
namespace wcf\system\cache\builder;
use wcf\data\package\Package;
use wcf\data\package\PackageList;

/**
 * Caches available controllers for case-insensitive lookup.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class ControllerCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @see	\wcf\system\cache\builder\AbstractCacheBuilder::rebuild()
	 */
	public function rebuild(array $parameters) {
		$data = array();
		$isACP = ($parameters['environment'] == 'admin');
		
		$packageList = new PackageList();
		$packageList->getConditionBuilder()->add("isApplication = ?", array(1));
		$packageList->readObjects();
		foreach ($packageList as $package) {
			$abbreviation = Package::getAbbreviation($package->package);
			$path = WCF_DIR . $package->packageDir . 'lib/' . ($isACP ? 'acp/' : '');
			
			$data[$abbreviation] = array(
				'action' => $this->getControllers($path, $abbreviation, 'action', $isACP),
				'form' => $this->getControllers($path, $abbreviation, 'form', $isACP),
				'page' => $this->getControllers($path, $abbreviation, 'page', $isACP)
			);
		}
		
		return $data;
	}
	
	/**
	 * Returns a list of case-insensitive controllers with their fully-qualified namespace grouped by type.
	 * 
	 * @param	string		$path
	 * @param	string		$abbreviation
	 * @param	string		$type
	 * @param	boolean		$isACP
	 * @return	array<string>
	 */
	protected function getControllers($path, $abbreviation, $type, $isACP) {
		$controllers = array();
		$path .= $type . '/';
		
		if (glob($path . '*' . ucfirst($type) . '.class.php') === false) return $controllers; 
		
		foreach (glob($path . '*' . ucfirst($type) . '.class.php') as $file) {
			$file = basename($file);
			if (preg_match('~^([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)(Action|Form|Page)\.class\.php$~', $file, $match)) {
				if ($match[1] === 'I') {
					continue;
				}
				
				$controller = mb_strtolower($match[1]);
				$fqn = '\\' . $abbreviation . '\\' . ($isACP ? 'acp\\' : '') . $type . '\\' . $match[1] . $match[2];
				
				$controllers[$controller] = $fqn;
			}
		}
		
		return $controllers;
	}
}
