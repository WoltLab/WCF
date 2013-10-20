<?php
namespace wcf\system\user\authentication;
use wcf\data\user\User;

/**
 * User authentication implementation that uses the e-mail address to identify users.
 * 
 * @author	Markus Bartz
 * @copyright	2011 Markus Bartz
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.user.authentication
 * @category	Community Framework
 */
class EmailUserAuthentication extends DefaultUserAuthentication {
	/**
	 * @see	\wcf\system\user\authentication\DefaultUserAuthentication::getUserByLogin()
	 */
	protected function getUserByLogin($login) {
		return User::getUserByEmail($login);
	}
}
