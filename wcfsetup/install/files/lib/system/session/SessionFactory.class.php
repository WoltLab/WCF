<?php
namespace wcf\system\session;
use wcf\util\HeaderUtil;

/**
 * Handles the session of the active user.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.session
 * @category	Community Framework
 */
class SessionFactory extends ACPSessionFactory {
	/**
	 * @see	\wcf\system\session\ACPSessionFactory::$sessionEditor
	 */
	protected $sessionEditor = 'wcf\data\session\SessionEditor';
	
	/**
	 * @see	\wcf\system\session\ACPSessionFactory::readSessionID()
	 */
	protected function readSessionID() {
		$sessionID = parent::readSessionID();
		
		// get sessionID from cookie
		if (empty($sessionID) && isset($_COOKIE[COOKIE_PREFIX.'cookieHash'])) {
			$sessionID = $_COOKIE[COOKIE_PREFIX . 'cookieHash'];
		}
		
		return $sessionID;
	}
	
	/**
	 * @see	\wcf\system\session\ACPSessionFactory::init()
	 */
	protected function init() {
		$usesCookies = true;
		
		if (isset($_COOKIE[COOKIE_PREFIX.'cookieHash'])) {
			if ($_COOKIE[COOKIE_PREFIX.'cookieHash'] != SessionHandler::getInstance()->sessionID) {
				$usesCookies = false;
			}
		}
		else {
			$usesCookies = false;
		}
		
		if (!$usesCookies) {
			// cookie support will be enabled upon next request
			HeaderUtil::setCookie('cookieHash', SessionHandler::getInstance()->sessionID);
		}
		else {
			// enable cookie support
			SessionHandler::getInstance()->enableCookies();
		}
		
		parent::init();
	}
}
