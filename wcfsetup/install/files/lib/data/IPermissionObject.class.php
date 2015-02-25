<?php
namespace wcf\data;

/**
 * Every object with permissions has to implement this interface.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data
 * @category	Community Framework
 */
interface IPermissionObject {
	/**
	 * Checks if the active user has the given permissions for this object and
	 * throws a PermissionDeniedException if they don't have one of the permissions.
	 * 
	 * @param	array<string>		$permissions
	 */
	public function checkPermissions(array $permissions);
	
	/**
	 * Returns the permission value of the given permission for this object
	 * and the active user.
	 * 
	 * @param	string			$permission
	 * @return	mixed
	 */
	public function getPermission($permission);
}
