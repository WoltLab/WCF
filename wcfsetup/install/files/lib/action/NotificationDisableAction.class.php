<?php
namespace wcf\action;
use wcf\data\user\notification\event\UserNotificationEvent;
use wcf\data\user\User;
use wcf\system\exception\IllegalLinkException;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\PasswordUtil;
use wcf\util\StringUtil;

/**
 * Allows a user to disable notifications by a direct link.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
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
	 * @var	\wcf\data\user\notification\event\UserNotificationEvent
	 */
	public $event = null;
	
	/**
	 * user id
	 * @var	integer
	 */
	public $userID = 0;
	
	/**
	 * user object
	 * @var	\wcf\data\user\User
	 */
	public $user = null;
	
	/**
	 * security token
	 * @var	string
	 */
	public $token = '';
	
	/**
	 * @see	\wcf\action\IAction::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['eventID'])) {
			$this->eventID = intval($_REQUEST['eventID']);
			
			$this->event = new UserNotificationEvent($this->eventID);
			if (!$this->event->eventID) {
				throw new IllegalLinkException();
			}
		}
		
		if (isset($_REQUEST['userID'])) $this->userID = intval($_REQUEST['userID']);
		$this->user = new User($this->userID);
		if (!$this->user->userID) {
			throw new IllegalLinkException();
		}
		
		if (isset($_REQUEST['token'])) $this->token = StringUtil::trim($_REQUEST['token']);
		if (empty($this->token) || !PasswordUtil::secureCompare($this->user->notificationMailToken, $this->token)) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see	\wcf\action\IAction::execute()
	 */
	public function execute() {
		parent::execute();
		
		if ($this->event !== null) {
			$sql = "UPDATE	wcf".WCF_N."_user_notification_event_to_user
				SET	mailNotificationType = ?
				WHERE	userID = ?
					AND eventID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array(
				'none',
				$this->userID,
				$this->eventID
			));
		}
		else {
			$sql = "UPDATE	wcf".WCF_N."_user_notification_event_to_user
				SET	mailNotificationType = ?
				WHERE	userID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array(
				'none',
				$this->userID
			));
		}
		
		$this->executed();
		
		// redirect to url
		HeaderUtil::delayedRedirect(LinkHandler::getInstance()->getLink(), WCF::getLanguage()->get('wcf.user.notification.mail.disabled'));
		exit;
	}
}
