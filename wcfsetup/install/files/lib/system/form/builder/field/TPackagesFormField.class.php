<?php
namespace wcf\system\form\builder\field;
use wcf\data\package\PackageCache;

/**
 * Provides default implementations of `IPackagesFormField` methods.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	3.2
 */
trait TPackagesFormField {
	/**
	 * ids of the packages considered for this field
	 * @var	int[]
	 */
	protected $__packageIDs = [];
	
	/**
	 * Returns the ids of the packages considered for this field. An empty
	 * array is returned if all packages are considered.
	 * 
	 * @return	int[]
	 */
	public function getPackageIDs() {
		return $this->__packageIDs;
	}
	
	/**
	 * Sets the ids of the packages considered for this field. If an empty
	 * array is given, all packages will be considered.
	 * 
	 * @param	int[]		$packageIDs
	 * @return	static
	 * 
	 * @throws	\InvalidArgumentException	if the given package ids are invalid
	 */
	public function packageIDs(array $packageIDs) {
		foreach ($packageIDs as $packageID) {
			if (PackageCache::getInstance()->getPackage($packageID) === null) {
				throw new \InvalidArgumentException("Unknown package with id '{$packageID}'.");
			}
		}
		
		$this->__packageIDs = $packageIDs;
		
		return $this;
	}
}
