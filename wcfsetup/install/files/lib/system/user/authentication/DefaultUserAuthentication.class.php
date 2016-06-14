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
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Authentication
 */
class DefaultUserAuthentication extends AbstractUserAuthentication {
	/**
	 * @inheritDoc
	 */
	public function supportsPersistentLogins() {
		return true;
	}
	
	/**
	 * @inheritDoc
	 */
	public function storeAccessData(User $user, $username, $password) {
		HeaderUtil::setCookie('userID', $user->userID, TIME_NOW + 365 * 24 * 3600);
		HeaderUtil::setCookie('password', PasswordUtil::getSaltedHash($password, $user->password), TIME_NOW + 365 * 24 * 3600);
	}
	
	/**
	 * @inheritDoc
	 */
	public function loginManually($username, $password, $userClassname = User::class) {
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
	 * @inheritDoc
	 */
	public function loginAutomatically($persistent = false, $userClassname = User::class) {
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
	protected function getUserAutomatically($userID, $password, $userClassname = User::class) {
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
