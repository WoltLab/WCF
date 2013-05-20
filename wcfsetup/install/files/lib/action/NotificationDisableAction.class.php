<?php
namespace wcf\action;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\notification\event\UserNotificationEvent;
use wcf\data\user\User;
use wcf\system\exception\IllegalLinkException;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;

/**
 * Allows a user to disable notifications by a direct link.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	action
 * @category	Community Framework
 */
class NotificationDisableAction extends AbstractAction {
	/**
	 * event id
	 * @var	integer
	 */
	public $eventID = 0;
	
	/**
	 * notification event
	 * @var	wcf\data\user\notification\event\UserNotificationEvent
	 */
	public $event = null;
	
	/**
	 * user id
	 * @var	integer
	 */
	public $userID = 0;
	
	/**
	 * user object
	 * @var	wcf\data\user\User
	 */
	public $user = null;
	
	/**
	 * security token
	 * @var	string
	 */
	public $token = '';
	
	/**
	 * @see	wcf\action\IAction::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['eventID'])) $this->eventID = intval($_REQUEST['eventID']);
		$this->event = new UserNotificationEvent($this->eventID);
		if (!$this->event->eventID) {
			throw new IllegalLinkException();
		}
		
		if (isset($_REQUEST['userID'])) $this->userID = intval($_REQUEST['userID']);
		$this->user = new User($this->userID);
		if (!$this->user->userID) {
			throw new IllegalLinkException();
		}
		
		if (isset($_REQUEST['token'])) $this->token = StringUtil::trim($_REQUEST['token']);
		if (empty($this->token) || $this->token != $this->user->notificationMailToken) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see	wcf\action\IAction::execute()
	 */
	public function execute() {
		parent::execute();
		
		// get object type
		$objectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.notification.notificationType', 'com.woltlab.wcf.notification.notificationType.mail');
		
		// delete notification setting
		$sql = "DELETE FROM	wcf".WCF_N."_user_notification_event_notification_type
			WHERE		userID = ?
					AND eventID = ?
					AND notificationTypeID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$this->userID,
			$this->eventID,
			$objectType->objectTypeID
		));
		$this->executed();
		
		// redirect to url
		HeaderUtil::delayedRedirect(LinkHandler::getInstance()->getLink(), WCF::getLanguage()->get('wcf.user.notification.mail.disabled'));
		exit;
	}
}
