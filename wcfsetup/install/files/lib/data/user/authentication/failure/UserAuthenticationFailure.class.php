<?php
namespace wcf\data\user\authentication\failure;
use wcf\data\DatabaseObject;
use wcf\util\UserUtil;
use wcf\system\WCF;

/**
 * Represents a user authentication failure.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.authentication.failure
 * @category	Community Framework
 *
 * @property-read	integer		$failureID
 * @property-read	string		$environment
 * @property-read	integer|null	$userID
 * @property-read	string		$username
 * @property-read	integer		$time
 * @property-read	string		$ipAddress
 * @property-read	string		$userAgent
 */
class UserAuthenticationFailure extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'user_authentication_failure';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'failureID';
	
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
		
		return $statement->fetchColumn();
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
		
		return $statement->fetchColumn();
	}
}
