<?php
namespace wcf\page;
use wcf\data\user\User;
use wcf\system\exception\IllegalLinkException;
use wcf\system\session\SessionHandler;
use wcf\system\WCF;
use wcf\util\PasswordUtil;
use wcf\util\StringUtil;

/**
 * Automatically authes the user for the current request via an access-token.
 * A missing token will be ignored, an invalid token results in a throw of a IllegalLinkException.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Page
 */
abstract class AbstractAuthedPage extends AbstractPage {
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		// check security token
		$this->checkAccessToken();
	}
	
	/**
	 * Validates the access-token and performs the login.
	 */
	protected function checkAccessToken() {
		if (isset($_REQUEST['at'])) {
			list($userID, $token) = array_pad(explode('-', StringUtil::trim($_REQUEST['at']), 2), 2, null);
			
			if (WCF::getUser()->userID) {
				if ($userID == WCF::getUser()->userID && PasswordUtil::secureCompare(WCF::getUser()->accessToken, $token)) {
					// everything is fine, but we are already logged in
					return;
				}
				else {
					// token is invalid
					throw new IllegalLinkException();
				}
			}
			else {
				$user = new User($userID);
				if (PasswordUtil::secureCompare($user->accessToken, $token)) {
					// token is valid -> change user
					SessionHandler::getInstance()->changeUser($user, true);
				}
				else {
					// token is invalid
					throw new IllegalLinkException();
				}
			}
		}
	}
}
