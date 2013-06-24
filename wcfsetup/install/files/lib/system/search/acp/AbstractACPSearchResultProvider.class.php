<?php
namespace wcf\system\search\acp;
use wcf\data\DatabaseObject;
use wcf\system\WCF;

/**
 * Abstract implementation of a ACP search result provider.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.search.acp
 * @category	Community Framework
 */
abstract class AbstractACPSearchResultProvider {
	/**
	 * Validates object options and permissions.
	 * 
	 * @param	wcf\data\DatabaseObject		$object
	 * @param	string				$optionsColumnName
	 * @param	string				$permissionsColumnName
	 * @return	boolean
	 */
	protected function validate(DatabaseObject $object, $optionsColumnName = 'options', $permissionsColumnName = 'permissions') {
		// check the options of this item
		$hasEnabledOption = true;
		if ($object->$optionsColumnName) {
			$hasEnabledOption = false;
			$options = explode(',', strtoupper($object->$optionsColumnName));
			foreach ($options as $option) {
				if (defined($option) && constant($option)) {
					$hasEnabledOption = true;
					break;
				}
			}
		}
		if (!$hasEnabledOption) return false;
		
		// check the permission of this item for the active user
		$hasPermission = true;
		if ($object->$permissionsColumnName) {
			$hasPermission = false;
			$permissions = explode(',', $object->$permissionsColumnName);
			foreach ($permissions as $permission) {
				if (WCF::getSession()->getPermission($permission)) {
					$hasPermission = true;
					break;
				}
			}
		}
		if (!$hasPermission) return false;
		
		return true;
	}
}
