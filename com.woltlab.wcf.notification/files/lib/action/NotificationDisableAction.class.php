<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');
require_once(WCF_DIR.'lib/data/user/notification/Notification.class.php');
require_once(WCF_DIR.'lib/data/user/UserEditor.class.php');

/**
 * This action disables notifications
 *
 * @author	Oliver Kliebisch
 * @copyright	2009-2010 Oliver Kliebisch
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.wcf.user.notification
 * @subpackage	action
 * @category 	Community Framework
 */
class NotificationDisableAction extends AbstractAction {
	/**
	 * The id of the notification
	 *
	 * @var integer
	 */
	public $notificationID = 0;

	/**
	 * The notification object
	 *
	 * @var Notification
	 */
	public $notification = null;

	/**
	 * The user editor object
	 *
	 * @var UserEditor
	 */
	public $user = null;

	/**
	 * The mail token
	 *
	 * @var string
	 */
	public $token = '';

	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();

		if (!MODULE_USER_NOTIFICATION) {
			throw new IllegalLinkException();
		}

		if (isset($_REQUEST['notificationID']))  {
			$this->notificationID = intval($_REQUEST['notificationID']);

			$this->notification = new Notification($this->notificationID);
			// validate notification ID
			if (!$this->notification->notificationID) {
				throw new IllegalLinkException();
			}                       
		}
		
		if (isset($_REQUEST['userID'])) {
			$this->user = new UserEditor(intval($_REQUEST['userID']));

			// validate user ID
			if (!$this->user->userID) {
				throw new IllegalLinkException();
			}
		}

		if (isset($_REQUEST['token'])) {
			$this->token = StringUtil::trim($_REQUEST['token']);

			// validate token
			if (empty($this->token) || $this->user->notificationMailToken != $this->token) {
				throw new IllegalLinkException();
			}
		}

		if (!$this->user || !$this->notification || !$this->token) {
			throw new IllegalLinkException();
		}
	}

	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();

		// delete old entry if present
		$sql = "DELETE FROM     wcf".WCF_N."_user_notification_event_to_user
			WHERE           objectType = '".escapeString($this->notification->objectType)."'
			AND             eventName = '".escapeString($this->notification->eventName)."'
			AND             notificationType = 'mail'
			AND             enabled = 1
			AND             packageID = ".$this->notification->packageID."
			AND             userID = ".$this->user->userID;
		WCF::getDB()->sendQuery($sql);

		// insert new setting for this type
		$sql = "INSERT INTO     wcf".WCF_N."_user_notification_event_to_user
					(userID, packageID, objectType, eventName, notificationType, enabled)
			VALUES          (".$this->user->userID.",
					".$this->notification->packageID.",
					'".escapeString($this->notification->objectType)."',
					'".escapeString($this->notification->eventName)."',
					'mail',
					0)";
		WCF::getDB()->sendQuery($sql);

		$this->executed();

		WCF::getTPL()->assign(array(
			'url' => 'index.php'.SID_ARG_1ST,
			'message' => WCF::getLanguage()->get('wcf.user.notification.type.mail.disabled'),
			'wait' => 5
		));
		WCF::getTPL()->display('redirect');
		exit;                
	}

}
?>
