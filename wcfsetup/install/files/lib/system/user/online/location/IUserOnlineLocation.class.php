<?php
namespace wcf\system\user\online\location;
use wcf\data\user\online\UserOnline;

/**
 * Any page location class should implement this interface.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Online\Location
 * @deprecated	3.0
 */
interface IUserOnlineLocation {
	/**
	 * Caches the information of a page location.
	 * 
	 * @param	UserOnline	$user
	 */
	public function cache(UserOnline $user);
	
	/**
	 * Returns the information of a page location.
	 * 
	 * @param	UserOnline	$user
	 * @param	string		$languageVariable
	 * @return	string
	 */
	public function get(UserOnline $user, $languageVariable = '');
}
