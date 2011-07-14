<?php
// wcf imports
require_once(WCF_DIR.'lib/page/AbstractFeedPage.class.php');
require_once(WCF_DIR.'lib/data/user/notification/FeedNotificationList.class.php');

/**
 * Prints a list of notification in a rss or atom feed
 *
 * @author	Oliver Kliebisch
 * @copyright	2009-2010 Oliver Kliebisch
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.wcf.user.notification
 * @subpackage	page
 * @category 	Community Framework
 */
class UserNotificationFeedPage extends AbstractFeedPage {
	/**
	 * list of notifications
	 *
	 * @var FeedNotificationList
	 */
	public $notificationList = null;

	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();

		// check user login (only cookie login supported by now)
		if (WCF::getUser()->userID == 0) {
			throw new PermissionDeniedException();
		}

		// get notifications
		$this->notificationList = new FeedNotificationList();
		$this->notificationList->sqlConditions .= 'u.userID = '.WCF::getUser()->userID;
		$this->notificationList->sqlConditions .= ' AND notification.time > '.($this->hours ? (TIME_NOW - $this->hours * 3600) : (TIME_NOW - 30 * 86400));
	}

	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();

		$this->notificationList->sqlLimit = $this->limit;
		$this->notificationList->readObjects();
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
	 * @see Page::show()
	 */
	public function show() {
		if (!MODULE_USER_NOTIFICATION) {
			throw new IllegalLinkException();
		}

		parent::show();

		// send content
		WCF::getTPL()->display(($this->format == 'atom' ? 'userNotificationFeedAtom' : 'userNotificationFeedRss2'), false);
		exit;
	}
}
?>