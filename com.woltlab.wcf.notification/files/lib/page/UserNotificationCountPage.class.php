<?php
// wcf imports
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');
require_once(WCF_DIR.'lib/data/user/NotificationUser.class.php');

/**
 * Outputs the number of outstanding notifications
 *
 * @author	Oliver Kliebisch
 * @copyright	2009-2010 Oliver Kliebisch
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.wcf.user.notification
 * @subpackage	page
 * @category 	Community Framework
 */
class UserNotificationCountPage extends AbstractPage {
	/**
	 * The notification user object
	 *
	 * @var NotificationUser
	 */
	public $user = null;

	/**
	 * @see AbstractPage::construct()
	 */
	public function __construct() {
		WCF::getSession()->disableUpdate();

		parent::__construct();
	}

	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();

		if (!MODULE_USER_NOTIFICATION || !WCF::getUser()->userID) {
			throw new IllegalLinkException();
		}

		$this->user = new NotificationUser(null, WCF::getUser(), false);
	}

	/**
	 * @see Page::show()
	 */
	public function show() {
		parent::show();

		HeaderUtil::sendHeaders();
		echo $this->user->hasOutstandingNotifications();
		exit;
	}
}
?>