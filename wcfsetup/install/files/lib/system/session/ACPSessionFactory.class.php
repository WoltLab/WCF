<?php
namespace wcf\system\session;
use wcf\system\event\EventHandler;

/**
 * Handles the ACP session of the active user.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.session
 * @category	Community Framework
 */
class ACPSessionFactory {
	/**
	 * session editor class name
	 * @var	string
	 */
	protected $sessionEditor = 'wcf\data\acp\session\ACPSessionEditor';
	
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
	 * Initializes the session system.
	 */
	protected function init() {
		SessionHandler::getInstance()->initSession();
	}
	
	/**
	 * Returns the session id from request (GET/POST). Returns an empty string,
	 * if no session id was given.
	 * 
	 * @return	string
	 */
	protected function readSessionID() {
		if (isset($_GET['s'])) {
			if (is_string($_GET['s'])) {
				return $_GET['s'];
			}
		}
		else if (isset($_POST['s'])) {
			if (is_string($_POST['s'])) {
				return $_POST['s'];
			}
		}
		
		return '';
	}
}
