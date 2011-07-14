<?php
namespace wcf\system\auth;
use wcf\data\user\User;
use wcf\system\exception\UserInputException;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;

/**
 * Default implementation of the user authentication.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.auth
 * @category 	Community Framework
 */
class UserAuthDefault extends UserAuth {
	/**
	 * @see UserAuth::supportsPersistentLogins()
	 */
	public function supportsPersistentLogins() {
		return true;
	}
	
	/**
	 * @see UserAuth::loginManually()
	 */
	public function loginManually($username, $password, $userClassname = 'wcf\data\user\User') {
		$user = User::getUserByUsername($username);
		$userSession = new $userClassname(null, null, $user);
		
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
	 * @see UserAuth::storeAccessData()
	 */
	public function storeAccessData(User $user, $username, $password) {
		HeaderUtil::setCookie('userID', $user->userID, TIME_NOW + 365 * 24 * 3600);
		HeaderUtil::setCookie('password', StringUtil::getSaltedHash($password, $user->salt), TIME_NOW + 365 * 24 * 3600);
	}

	/**
	 * @see UserAuth::loginAutomatically()
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
	 * Returns a user object or null on failure.
	 * 
	 * @param	integer		$userID
	 * @param	string		$password
	 * @param	string		$userClassname
	 * @return	User	
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
	 * @param	User		$user
	 * @param	string		$password
	 * @return	boolean
	 */
	protected function checkCookiePassword($user, $password) {
		return $user->checkCookiePassword($password);
	}
}
