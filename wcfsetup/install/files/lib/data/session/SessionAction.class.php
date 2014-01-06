<?php
namespace wcf\data\session;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\session\SessionHandler;
use wcf\system\WCF;

/**
 * Executes session-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
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
	 * Validates the 'keepAlive' action.
	 */
	public function validateKeepAlive() {
		// does nothing
	}
	
	/**
	 * Updates session's last activity time to prevent it from expiring.
	 */
	public function keepAlive() {
		// ignore sessions created by this request
		if (WCF::getSession()->lastActivityTime == TIME_NOW) {
			return;
		}
		
		SessionHandler::getInstance()->keepAlive();
	}
}
