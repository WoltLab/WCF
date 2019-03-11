<?php
namespace wcf\system\user\authentication;
use wcf\data\user\User;

/**
 * Every user authentication has to implement this interface.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Authentication
 */
interface IUserAuthentication {
	/**
	 * Returns an unique instance of the authentication class
	 * 
	 * @return	IUserAuthentication
	 */
	public static function getInstance();
	
	/**
	 * Returns true if this authentication supports persistent logins.
	 * 
	 * @return	boolean
	 */
	public function supportsPersistentLogins();
	
	/**
	 * Stores the user access data for a persistent login.
	 * 
	 * @param	User		$user
	 * @param	string		$username
	 * @param	string		$password
	 */
	public function storeAccessData(User $user, $username, $password);
	
	/**
	 * Does a manual user login or `null` if login was unsuccessful.
	 * 
	 * @param	string		$username
	 * @param	string		$password
	 * @param	string		$userClassname		class name of user class
	 * @return	User|null
	 */
	public function loginManually($username, $password, $userClassname = User::class);
	
	/**
	 * Does a user login automatically or `null` if login was unsuccessful.
	 * 
	 * @param	boolean		$persistent		true = persistent login
	 * @param	string		$userClassname		class name of user class
	 * @return	User|null
	 */
	public function loginAutomatically($persistent = false, $userClassname = User::class);
}
