<?php
namespace wcf\data\user\authentication\failure;
use wcf\data\DatabaseObject;
use wcf\util\UserUtil;
use wcf\system\WCF;

/**
 * Represents a user authentication failure.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User\Authentication\Failure
 *
 * @property-read	integer		$failureID		unique if of the user authentication failure
 * @property-read	string		$environment		environment in which the user authentication failure occurred, possible values: 'user' or 'admin'
 * @property-read	integer|null	$userID			id of the user using an incorrect password or null if the provided username or email address is not associated with any registered user
 * @property-read	string		$username		user name or email address used to login
 * @property-read	integer		$time			timestamp at which the user authentication failure has occurred
 * @property-read	string		$ipAddress		ip address of the user trying to login in
 * @property-read	string		$userAgent		user agent of the user trying to login in
 */
class UserAuthenticationFailure extends DatabaseObject {
	/**
	 * Returns the ip address and attempts to convert into IPv4.
	 * 
	 * @return	string
	 */
	public function getIpAddress() {
		return UserUtil::convertIPv6To4($this->ipAddress);
	}
	
	/**
	 * Returns the number of authentication failures caused by given ip address.
	 * 
	 * @param	string		$ipAddress
	 * @return	boolean
	 */
	public static function countIPFailures($ipAddress) {
		$sql = "SELECT	COUNT(*)
			FROM	wcf".WCF_N."_user_authentication_failure
			WHERE	ipAddress = ?
				AND time > ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$ipAddress, TIME_NOW - USER_AUTHENTICATION_FAILURE_TIMEOUT]);
		
		return $statement->fetchSingleColumn();
	}
	
	/**
	 * Returns the number of authentication failures for given user account.
	 * 
	 * @param	integer		$userID
	 * @return	boolean
	 */
	public static function countUserFailures($userID) {
		$sql = "SELECT	COUNT(*)
			FROM	wcf".WCF_N."_user_authentication_failure
			WHERE	userID = ?
				AND time > ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$userID, TIME_NOW - USER_AUTHENTICATION_FAILURE_TIMEOUT]);
		
		return $statement->fetchSingleColumn();
	}
}
