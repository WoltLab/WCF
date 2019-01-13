<?php
namespace wcf\system\form\builder\field;

/**
 * Represents a form field that, in some way, considers packages that may be passed
 * to the field by their ids.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	5.2
 */
interface IPackagesFormField {
	/**
	 * Returns the ids of the packages considered for this field. An empty
	 * array is returned if all packages are considered.
	 * 
	 * @return	int[]
	 */
	public function getPackageIDs();
	
	/**
	 * Sets the ids of the packages considered for this field. If an empty
	 * array is given, all packages will be considered.
	 * 
	 * @param	int[]		$packageIDs
	 * @return	static
	 * 
	 * @throws	\InvalidArgumentException	if the given package ids are invalid
	 */
	public function packageIDs(array $packageIDs);
}
