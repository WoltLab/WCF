<?php
namespace wcf\system\session;
use wcf\data\acp\session\ACPSessionEditor;
use wcf\data\session\Session;
use wcf\system\event\EventHandler;

/**
 * Handles the ACP session of the active user.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Session
 */
class ACPSessionFactory {
	/**
	 * @deprecated 5.4 - This property is not read any longer.
	 */
	protected $cookieSuffix = 'acp_';
	
	/**
	 * @deprecated 5.4 - This property is not read any longer.
	 */
	protected $sessionEditor = ACPSessionEditor::class;
	
	/**
	 * Loads the object of the active session.
	 */
	public function load() {
		SessionHandler::getInstance()->loadFromCookie();
		
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
	 * @deprecated 5.4 - Sessions are fully managed by SessionHandler.
	 */
	public function hasValidCookie() {
		return SessionHandler::getInstance()->hasValidCookie();
	}
	
	/**
	 * Initializes the session system.
	 */
	protected function init() {
		SessionHandler::getInstance()->initSession();
	}
}
