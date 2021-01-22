<?php
namespace wcf\data;
use wcf\system\WCF;

/**
 * Provides a method for validating database object permissions.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data
 * @since	3.0
 */
trait TDatabaseObjectPermissions {
	/**
	 * Returns true if the active user has at least one permission required
	 * by this object.
	 * 
	 * @return	bool
	 */
	public function validatePermissions() {
		if ($this->permissions) {
			$permissions = explode(',', $this->permissions);
			foreach ($permissions as $permission) {
				if (WCF::getSession()->getPermission($permission)) {
					return true;
				}
			}
			
			return false;
		}
		
		return true;
	}
}
