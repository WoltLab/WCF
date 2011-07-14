<?php
// wcf imports
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');
require_once(WCF_DIR.'lib/data/user/notification/NotificationList.class.php');
require_once(WCF_DIR.'lib/data/user/NotificationUser.class.php');

/**
 * Shows the user panel notification link
 *
 * @author      Oliver Kliebisch
 * @copyright   2009-2010 Oliver Kliebisch
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     com.woltlab.community.wcf.user.notification
 * @subpackage  system.event.listener
 * @category    Community Framework
 */
class StructuredTemplateUserPanelNotificationListener implements EventListener {
	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		// do nothing for guests and spiders or if module is deactivated
		if (!USER_NOTIFICATION_USER_MENU_LINK_ACTIVE || WCF::getUser()->userID == 0 || !MODULE_USER_NOTIFICATION) return;

		$user = new NotificationUser(null, WCF::getUser(), false);

		WCF::getTPL()->assign('notificationUser', $user);

		WCF::getTPL()->append(array(
			'additionalUserMenuItems' => WCF::getTPL()->fetch('userMenuNotificationLink'),
			'additionalHeaderContents' => USER_NOTIFICATION_USER_MENU_LINK_AUTOREFRESH ? WCF::getTPL()->fetch('userMenuPeriodicalExecuter') : ''
		));
	}
}
?>