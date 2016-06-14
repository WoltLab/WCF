<?php
namespace wcf\data;
use wcf\system\exception\PermissionDeniedException;

/**
 * Every object with permissions has to implement this interface.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data
 */
interface IPermissionObject {
	/**
	 * Checks if the active user has the given permissions for this object.
	 * 
	 * @param	string[]	$permissions
	 * @throws	PermissionDeniedException	if the active user does not have at least one of the given permissions.
	 */
	public function checkPermissions(array $permissions);
	
	/**
	 * Returns the permission value of the given permission for this object
	 * and the active user.
	 * 
	 * @param	string		$permission
	 * @return	mixed
	 */
	public function getPermission($permission);
}
