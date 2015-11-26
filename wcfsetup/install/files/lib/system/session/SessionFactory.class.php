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
	 * @see	\wcf\system\session\ACPSessionFactory::hasValidCookie()
	 */
	public function hasValidCookie() {
		if (isset($_COOKIE[COOKIE_PREFIX.'cookieHash'])) {
			if ($_COOKIE[COOKIE_PREFIX.'cookieHash'] == SessionHandler::getInstance()->sessionID) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * @see	\wcf\system\session\ACPSessionFactory::readSessionID()
	 */
	protected function readSessionID() {
		// get sessionID from cookie
		if (isset($_COOKIE[COOKIE_PREFIX.'cookieHash'])) {
			return $_COOKIE[COOKIE_PREFIX . 'cookieHash'];
		}
		
		return '';
	}
	
	/**
	 * @see	\wcf\system\session\ACPSessionFactory::init()
	 */
	protected function init() {
		if (!$this->hasValidCookie()) {
			// cookie support will be enabled upon next request
			HeaderUtil::setCookie('cookieHash', SessionHandler::getInstance()->sessionID);
		}
		
		// enable cookie support
		
		SessionHandler::getInstance()->enableCookies();
		
		parent::init();
	}
}
