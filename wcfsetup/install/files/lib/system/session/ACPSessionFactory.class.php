<?php
namespace wcf\system\session;
use wcf\system\event\EventHandler;

/**
 * ACPSessionFactory handles session for active user.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.session
 * @category 	Community Framework
 */
class ACPSessionFactory {
	/**
	 * session editor class name
	 * @var	string
	 */	
	protected $sessionEditor = 'wcf\data\acp\session\ACPSessionEditor';
	
	/**
	 * session data editor class name
	 * @var	string
	 */
	protected $sessionDataEditor = 'wcf\data\acp\session\data\ACPSessionDataEditor';
	
	/**
	 * Loads the object of the active session.
	 */	
	public function load() {
		// get session
		$sessionID = $this->readSessionID();
		SessionHandler::getInstance()->load($this->sessionEditor, $this->sessionDataEditor, $sessionID);
		
		// call shouldInit event
		if (!defined('NO_IMPORTS')) {
			EventHandler::getInstance()->fireAction($this, 'shouldInit');
		}
		
		$this->init();
		
		// call didInit event
		if (!defined('NO_IMPORTS')) {
			EventHandler::getInstance()->fireAction($this, 'didInit');
		}
	}
	
	/**
	 * Initializes the session system.
	 */
	protected function init() {
		SessionHandler::getInstance()->initSession();
	}
	
	/**
	 * Gets the sessionID from request (GET/POST). Returns an empty string,
	 * if no sessionID was given.
	 *
	 * @return	string
	 *
	 */	
	protected function readSessionID() {
		if (isset($_GET['s'])) {
			return $_GET['s'];
		}
		else if (isset($_POST['s'])) {
			return $_POST['s'];
		}
		
		return '';
	}
}
