<?php
namespace wcf\system\user\authentication;
use wcf\data\user\User;

/**
 * User authentication implementation that uses the e-mail address to identify users.
 * 
 * @author	Markus Bartz
 * @copyright	2011 Markus Bartz
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Authentication
 */
class EmailUserAuthentication extends DefaultUserAuthentication {
	/**
	 * @inheritDoc
	 */
	protected function getUserByLogin($login) {
		return User::getUserByEmail($login);
	}
}
