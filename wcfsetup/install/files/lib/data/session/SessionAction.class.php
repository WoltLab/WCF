<?php
namespace wcf\data\session;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\event\EventHandler;
use wcf\system\session\SessionHandler;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\WCF;

/**
 * Executes session-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.session
 * @category	Community Framework
 */
class SessionAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$allowGuestAccess
	 */
	protected $allowGuestAccess = array('keepAlive');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\session\SessionEditor';
	
	/**
	 * list of data values returned upon a keep alive request
	 * @var	array<mixed>
	 */
	public $keepAliveData = array();
	
	/**
	 * Validates the 'keepAlive' action.
	 */
	public function validateKeepAlive() {
		// does nothing
	}
	
	/**
	 * Updates session's last activity time to prevent it from expiring. In addition this method
	 * will return updated counters for notifications and 3rd party components.
	 * 
	 * @return	array<mixed>
	 */
	public function keepAlive() {
		// ignore sessions created by this request
		if (WCF::getSession()->lastActivityTime == TIME_NOW) {
			return;
		}
		
		// update last activity time
		SessionHandler::getInstance()->keepAlive();
		
		// update notification counts
		$this->keepAliveData = array(
			'userNotificationCount' => UserNotificationHandler::getInstance()->getNotificationCount(true)
		);
		
		// notify 3rd party components
		EventHandler::getInstance()->fireAction($this, 'keepAlive');
		
		return $this->keepAliveData;
	}
}
