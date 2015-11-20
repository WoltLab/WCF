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
	 * @var EventHandler
	 */
	protected $eventHandler;
	
	/**
	 * session editor class name
	 * @var	string
	 */
	protected $sessionEditor = 'wcf\data\acp\session\ACPSessionEditor';
	
	/**
	 * @var SessionHandler
	 */
	protected $sessionHandler;
	
	/**
	 * ACPSessionFactory constructor.
	 * 
	 * @param       EventHandler    $eventHandler
	 * @param       SessionHandler  $sessionHandler
	 */
	public function __construct(EventHandler $eventHandler, SessionHandler $sessionHandler) {
		$this->eventHandler = $eventHandler;
		$this->sessionHandler = $sessionHandler;
	}
	
	/**
	 * Loads the object of the active session.
	 */
	public function load() {
		// get session
		$sessionID = $this->readSessionID();
		$this->sessionHandler->load($this->sessionEditor, $sessionID);
		
		// call beforeInit event
		if (!defined('NO_IMPORTS')) {
			$this->eventHandler->fireAction($this, 'beforeInit');
		}
		
		$this->init();
		
		// call afterInit event
		if (!defined('NO_IMPORTS')) {
			$this->eventHandler->fireAction($this, 'afterInit');
		}
	}
	
	/**
	 * Returns true if session was based upon a valid cookie.
	 * 
	 * @return	boolean
	 */
	public function hasValidCookie() {
		return false;
	}
	
	/**
	 * Initializes the session system.
	 */
	protected function init() {
		$this->sessionHandler->initSession();
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
