<?php
namespace wcf\system\auth;
use wcf\data\user\User;
use wcf\system\exception\UserInputException;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;

/**
 * Implementation of the user authentication, that uses the e-mail address instead of the username to identify the user.
 * 
 * @author	Markus Bartz
 * @copyright	2011 Markus Bartz
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.auth
 * @category 	Community Framework
 */
class UserAuthEmail extends UserAuthDefault {
	/**
	 * @see	wcf\system\auth\UserAuth::loginManually()
	 * 
	 * In this case $username is indeed not the username, but the e-mail address.
	 */
	public function loginManually($username, $password, $userClassname = 'wcf\data\user\User') {
		$user = User::getUserByEmail($username);
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
}
