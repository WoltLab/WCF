<?php
namespace wcf\form;

use wcf\data\user\notification\event\UserNotificationEvent;
use wcf\data\user\User;
use wcf\system\exception\IllegalLinkException;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;

/**
 * Represents the notification unsubscribe form.
 *
 * @author	Tim Duesterhus
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Form
 * @since	5.3
 */
class NotificationUnsubscribeForm extends AbstractForm {
	/**
	 * @var	User
	 */
	public $user;
	
	/**
	 * user provided verification token
	 * @var	string
	 */
	public $token = '';
	
	/**
	 * @var	boolean
	 */
	public $isOneClick = false;
	
	/**
	 * notification event to unsubscribe
	 * @var	UserNotificationEvent
	 */
	public $event;
	
	/**
	 * Disable security token validation.
	 */
	protected function validateSecurityToken() {
		// Do not validate the security token, the request is authenticated by
		// the mail token.
	}
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['userID'])) {
			$this->user = new User(intval($_REQUEST['userID']));
			if (!$this->user->userID) {
				throw new IllegalLinkException();
			}
		}
		else {
			throw new IllegalLinkException();
		}
		
		if (isset($_REQUEST['token'])) {
			$this->token = StringUtil::trim($_REQUEST['token']);
		}
		else {
			throw new IllegalLinkException();
		}
		
		if (!empty($_REQUEST['eventID'])) {
			$this->event = new UserNotificationEvent(intval($_REQUEST['eventID']));
			if (!$this->event->eventID) {
				throw new IllegalLinkException();
			}
		}
		
		if (!hash_equals($this->user->notificationMailToken, $this->token)) {
			throw new IllegalLinkException();
		}
		
		$this->isOneClick = (isset($_POST['List-Unsubscribe']) && $_POST['List-Unsubscribe'] === 'One-Click');
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		if ($this->event !== null) {
			$sql = "UPDATE	wcf".WCF_N."_user_notification_event_to_user
				SET	mailNotificationType = ?
				WHERE	userID = ?
					AND eventID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([
				'none',
				$this->user->userID,
				$this->event->eventID
			]);
		}
		else {
			$sql = "UPDATE	wcf".WCF_N."_user_notification_event_to_user
				SET	mailNotificationType = ?
				WHERE	userID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([
				'none',
				$this->user->userID
			]);
		}
		
		$this->saved();
		
		if ($this->isOneClick) {
			// One-Click unsubscriptions are sent by the recipient's MUA upon clicking a button.
			// No additional information except the URI are available and specifically no user interaction can happen.
			// Just send a lightweight 204 No Content response, instead of kilobytes of HTML to save on resources.
			header('HTTP/1.0 204 No Content');
			exit;
		}
		else {
			// redirect to url
			HeaderUtil::delayedRedirect(LinkHandler::getInstance()->getLink(), WCF::getLanguage()->get('wcf.user.notification.mail.disabled'));
			exit;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'user' => $this->user,
			'token' => $this->token,
			'event' => $this->event,
		]);
	}
}
