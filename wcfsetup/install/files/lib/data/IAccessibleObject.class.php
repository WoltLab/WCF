<?php
namespace wcf\data;
use wcf\data\user\User;

/**
 * Interface for objects whose access is restrictable so that access for every user
 * has to be checked separately.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data
 * @since	3.0
 */
interface IAccessibleObject {
	/**
	 * Returns `true` if the given user can access the object.
	 * 
	 * @param	User	$user	checked user, if `null` active user is used instead
	 * @return	boolean
	 */
	public function isAccessible(User $user = null);
}
