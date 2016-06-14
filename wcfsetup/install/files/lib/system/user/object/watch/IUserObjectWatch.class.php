<?php
namespace wcf\system\user\object\watch;

/**
 * Any watchable object type should implement this interface.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Object\Watch
 */
interface IUserObjectWatch {
	/**
	 * Validates the given object id. Throws an exception on error.
	 * 
	 * @param	integer		$objectID
	 * @throws	\wcf\system\exception\UserException
	 */
	public function validateObjectID($objectID);
	
	/**
	 * Resets the user storage for given users.
	 * 
	 * @param	integer[]		$userIDs
	 */
	public function resetUserStorage(array $userIDs);
}
