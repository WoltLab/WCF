<?php
namespace wcf\system\session;
use wcf\data\acp\session\ACPSessionEditor;
use wcf\system\event\EventHandler;
use wcf\util\HeaderUtil;

/**
 * Handles the ACP session of the active user.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Session
 */
class ACPSessionFactory {
	/**
	 * suffix used to tell ACP and frontend cookies apart
	 * @var string
	 */
	protected $cookieSuffix = '_acp';
	
	/**
	 * session editor class name
	 * @var	string
	 */
	protected $sessionEditor = ACPSessionEditor::class;
	
	/**
	 * Loads the object of the active session.
	 */
	public function load() {
		// get session
		$sessionID = $this->readSessionID();
		SessionHandler::getInstance()->load($this->sessionEditor, $sessionID);
		
		// call beforeInit event
		if (!defined('NO_IMPORTS')) {
			EventHandler::getInstance()->fireAction($this, 'beforeInit');
		}
		
		$this->init();
		
		// call afterInit event
		if (!defined('NO_IMPORTS')) {
			EventHandler::getInstance()->fireAction($this, 'afterInit');
		}
	}
	
	/**
	 * Returns true if session was based upon a valid cookie.
	 * 
	 * @return	boolean
	 * @since	3.0
	 */
	public function hasValidCookie() {
		if (isset($_COOKIE[COOKIE_PREFIX.'cookieHash'.$this->cookieSuffix])) {
			if ($_COOKIE[COOKIE_PREFIX.'cookieHash'.$this->cookieSuffix] == SessionHandler::getInstance()->sessionID) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Initializes the session system.
	 */
	protected function init() {
		if (!$this->hasValidCookie()) {
			// cookie support will be enabled upon next request
			HeaderUtil::setCookie('cookieHash'.$this->cookieSuffix, SessionHandler::getInstance()->sessionID);
		}
		
		SessionHandler::getInstance()->initSession();
	}
	
	/**
	 * Returns the session id from cookie. Returns an empty string,
	 * if no session cookie was provided.
	 * 
	 * @return	string
	 */
	protected function readSessionID() {
		// get sessionID from cookie
		if (isset($_COOKIE[COOKIE_PREFIX.'cookieHash'.$this->cookieSuffix])) {
			return $_COOKIE[COOKIE_PREFIX . 'cookieHash'.$this->cookieSuffix];
		}
		
		return '';
	}
}
