<?php
namespace wcf\system\user\authentication;
use wcf\data\user\User;
use wcf\system\exception\UserInputException;
use wcf\util\HeaderUtil;
use wcf\util\PasswordUtil;

/**
 * Default user authentication implementation that uses the username to identify users.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.user.authentication
 * @category	Community Framework
 */
class DefaultUserAuthentication extends AbstractUserAuthentication {
	/**
	 * @see	\wcf\system\user\authentication\IUserAuthentication::supportsPersistentLogins()
	 */
	public function supportsPersistentLogins() {
		return true;
	}
	
	/**
	 * @see	\wcf\system\user\authentication\IUserAuthentication::storeAccessData()
	 */
	public function storeAccessData(User $user, $username, $password) {
		HeaderUtil::setCookie('userID', $user->userID, TIME_NOW + 365 * 24 * 3600);
		HeaderUtil::setCookie('password', PasswordUtil::getSaltedHash($password, $user->password), TIME_NOW + 365 * 24 * 3600);
	}
	
	/**
	 * @see	\wcf\system\user\authentication\IUserAuthentication::loginManually()
	 */
	public function loginManually($username, $password, $userClassname = 'wcf\data\user\User') {
		$user = $this->getUserByLogin($username);
		$userSession = (get_class($user) == $userClassname ? $user : new $userClassname(null, null, $user));
		
		if ($userSession->userID == 0) {
			throw new UserInputException('username', 'notFound');
		}
		
		// check password
		if (!$userSession->checkPassword($password)) {
			throw new UserInputException('password', 'false');
		}
		
		return $userSession;
	}
	
	/**
	 * @see	\wcf\system\user\authentication\IUserAuthentication::loginAutomatically()
	 */
	public function loginAutomatically($persistent = false, $userClassname = 'wcf\data\user\User') {
		if (!$persistent) return null;
		
		$user = null;
		if (isset($_COOKIE[COOKIE_PREFIX.'userID']) && isset($_COOKIE[COOKIE_PREFIX.'password'])) {
			if (!($user = $this->getUserAutomatically(intval($_COOKIE[COOKIE_PREFIX.'userID']), $_COOKIE[COOKIE_PREFIX.'password'], $userClassname))) {
				$user = null;
				// reset cookie
				HeaderUtil::setCookie('userID', '');
				HeaderUtil::setCookie('password', '');
			}
		}
		
		return $user;
	}
	
	/**
	 * Returns a user object by given login name.
	 * 
	 * @param	string			$login
	 * @return	\wcf\data\user\User
	 */
	protected function getUserByLogin($login) {
		return User::getUserByUsername($login);
	}
	
	/**
	 * Returns a user object or null on failure.
	 * 
	 * @param	integer		$userID
	 * @param	string		$password
	 * @param	string		$userClassname
	 * @return	\wcf\data\user\User
	 */
	protected function getUserAutomatically($userID, $password, $userClassname = 'wcf\data\user\User') {
		$user = new $userClassname($userID);
		if (!$user->userID || !$this->checkCookiePassword($user, $password)) {
			$user = null;
		}
		
		return $user;
	}
	
	/**
	 * Validates the cookie password.
	 * 
	 * @param	\wcf\data\user\User	$user
	 * @param	string			$password
	 * @return	boolean
	 */
	protected function checkCookiePassword($user, $password) {
		return $user->checkCookiePassword($password);
	}
}
