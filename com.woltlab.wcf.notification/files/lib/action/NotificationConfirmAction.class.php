<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractSecureAction.class.php');
require_once(WCF_DIR.'lib/data/user/notification/NotificationEditor.class.php');
require_once(WCF_DIR.'lib/data/user/notification/NotificationList.class.php');
require_once(WCF_DIR.'lib/data/user/NotificationUser.class.php');

/**
 * This action handles default confirmations of notifications
 *
 * @author	Oliver Kliebisch
 * @copyright	2009-2010 Oliver Kliebisch
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.wcf.user.notification
 * @subpackage	action
 * @category 	Community Framework
 */
class NotificationConfirmAction extends AbstractSecureAction {
	/**
	 * The id of the notification
	 *
	 * @var integer
	 */
	public $notificationID = 0;

	/**
	 * The notification editor object
	 *
	 * @var NotificationEditor
	 */
	public $notification = null;

	/**
	 * The url for redirection (non-ajax)
	 *
	 * @var string
	 */
	public $url = null;

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

			$this->notification = new NotificationEditor($this->notificationID);
			// validate notification ID
			if (!$this->notification->notificationID) {
				throw new IllegalLinkException();
			}
			$this->notification->userID = explode(',', $this->notification->userID);
			// validate user
			if (!in_array(WCF::getUser()->userID, $this->notification->userID)) {
				throw new PermissionDeniedException();
			}
		}

		if (isset($_REQUEST['url'])) $this->url = rawurldecode(StringUtil::trim($_REQUEST['url']));
	}

	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();

		// get user
		$user = new NotificationUser(null, WCF::getUser(), false);

		// mark single notification
		if ($this->notificationID) {
			if ($this->notification->confirmed) {
				throw new NamedUserException(WCF::getLanguage()->get('wcf.user.notification.error.alreadyAccepted'));
			}

			$this->notification->markConfirmed();
		}
		// mark all outstanding notifications
		else {
			if ($user->hasOutstandingNotifications()) {
				// get outstanding notifications
				$notificationList = new NotificationList();
				$notificationList->sqlConditions = "notification.userID = ".$user->userID;
				$notificationList->readObjects();

				$notifications = $notificationList->getObjects();
				$notificationIDArray = array();

				foreach ($notifications as $notification) {
					if (!$notification->confirmed && !$notification->acceptURL) {
						$notificationIDArray[] = $notification->notificationID;
					}
				}

				NotificationEditor::markAllConfirmed($notificationIDArray, array(WCF::getUser()->userID));
			}
		}

		// recalculate flags
		$user->recalculateOutstandingNotifications();

		$this->executed();
	}

	/**
	 * @see Action::executed()
	 */
	public function executed() {
		parent::executed();

		if (!isset($_REQUEST['ajax'])) {
			$this->checkURL();
			HeaderUtil::redirect($this->url);
		}
	}

	/**
	 * Gets the redirect url.
	 */
	protected function checkURL() {
		if (empty($this->url)) {
			$this->url = 'index.php'.SID_ARG_1ST;
		}
		// append missing session id
		else if (SID_ARG_1ST != '' && !preg_match('/(?:&|\?)s=[a-z0-9]{40}/', $this->url)) {
			if (StringUtil::indexOf($this->url, '?') !== false) $this->url .= SID_ARG_2ND_NOT_ENCODED;
			else $this->url .= SID_ARG_1ST;
		}
	}
}
?>
