<?php
// wcf imports
require_once(WCF_DIR.'lib/page/MultipleLinkPage.class.php');
require_once(WCF_DIR.'lib/page/util/menu/UserCPMenu.class.php');
require_once(WCF_DIR.'lib/data/user/notification/NotificationList.class.php');
require_once(WCF_DIR.'lib/data/user/notification/NotificationEditor.class.php');

/**
 * Shows the user notification center
 *
 * @author	Oliver Kliebisch
 * @copyright	2009-2010 Oliver Kliebisch
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.wcf.user.notification
 * @subpackage	page
 * @category 	Community Framework
 */
class UserNotificationPage extends MultipleLinkPage {
	// system
	public $templateName = 'userNotification';
	public $defaultSortField = 'time';

	/**
	 * The list of this users notifications
	 *
	 * @var NotificationList
	 */
	public $notificationList = null;

	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();

		if (!MODULE_USER_NOTIFICATION) {
			throw new IllegalLinkException();
		}

		$this->notificationList = new NotificationList();
		$this->notificationList->sqlConditions = "u.userID = ".WCF::getUser()->userID;
	}

	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();

		$this->notificationList->sqlSelects = "u.*,";
		$this->notificationList->sqlLimit = $this->itemsPerPage;
		$this->notificationList->sqlOffset = $this->itemsPerPage * ($this->pageNo - 1);
		$this->notificationList->sqlOrderBy = "notification.time DESC";
		$this->notificationList->readObjects();

		// mark certain notifications as confirmed by now
		$notificationIDArray = array();
		foreach($this->notificationList->getObjects() as $notification) {
			if (!$notification->confirmed && !$notification->event->acceptURL) {
				$notificationIDArray[] = $notification->notificationID;
			}
		}

		if (count($notificationIDArray)) {
			NotificationEditor::markAllConfirmed($notificationIDArray, array(WCF::getUser()->userID));
			$user = new NotificationUser(null, WCF::getUser(), false);
			$user->recalculateOutstandingNotifications();
		}
	}

	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();

		WCF::getTPL()->assign(array(
			'notifications' => $this->notificationList->getObjects()
		));
	}

	/**
	 * @see MultipleLinkPage::countItems()
	 */
	public function countItems() {
		parent::countItems();

		return $this->notificationList->countObjects();
	}

	/**
	 * @see Page::show()
	 */
	public function show() {
		if (!WCF::getUser()->userID) {
			throw new PermissionDeniedException();
		}

		// set active tab
		UserCPMenu::getInstance()->setActiveMenuItem('wcf.user.usercp.menu.link.management.notification');

		parent::show();
	}
}
?>