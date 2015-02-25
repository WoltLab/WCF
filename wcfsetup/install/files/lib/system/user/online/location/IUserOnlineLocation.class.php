<?php
namespace wcf\system\user\online\location;
use wcf\data\user\online\UserOnline;

/**
 * Any page location class should implement this interface.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.user.online.location
 * @category	Community Framework
 */
interface IUserOnlineLocation {
	/**
	 * Caches the information of a page location.
	 * 
	 * @param	\wcf\data\user\online\UserOnline		$user
	 */
	public function cache(UserOnline $user);
	
	/**
	 * Returns the information of a page location.
	 * 
	 * @param	\wcf\data\user\online\UserOnline		$user
	 * @param	string					$languageVariable
	 * @return	string
	 */
	public function get(UserOnline $user, $languageVariable = '');
}
