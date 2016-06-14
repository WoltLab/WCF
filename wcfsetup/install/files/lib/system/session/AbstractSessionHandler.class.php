<?php
namespace wcf\system\session;
use wcf\system\SingletonFactory;

/**
 * Abstract implementation for application-specific session handlers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Session
 */
abstract class AbstractSessionHandler extends SingletonFactory {
	/**
	 * SessionHandler object
	 * @var	\wcf\system\session\SessionHandler
	 */
	protected $sessionHandler = null;
	
	/**
	 * @inheritDoc
	 */
	protected final function init() {
		$this->sessionHandler = SessionHandler::getInstance();
		
		// initialize session
		$this->initSession();
	}
	
	/**
	 * Forwards calls on unknown properties to stored SessionHandler
	 * 
	 * @param	string		$key
	 * @return	mixed
	 */
	public function __get($key) {
		return $this->sessionHandler->{$key};
	}
	
	/**
	 * Initializes this session.
	 */
	abstract protected function initSession();
}
