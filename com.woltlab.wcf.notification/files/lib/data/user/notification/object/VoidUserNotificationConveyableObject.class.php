<?php
// wcf imports
require_once(WCF_DIR.'lib/data/user/notification/object/UserNotificationConveyableObject.class.php');

/**
 * This dummy object provides the usage of object-unbound notifications
 *
 * @author	Marcel Werk, Oliver Kliebisch
 * @copyright	2009-2011 WoltLab GmbH, Oliver Kliebisch
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.user.notification
 * @subpackage	data.user.notification.object
 * @category 	Community Framework
 */
class VoidUserNotificationConveyableObject implements UserNotificationConveyableObject {
	/**
	 * @see UserNotificationConveyableObject::getIcon()
	 */
	public function getIcon() {
		return '';
	}

	/**
	 * @see UserNotificationConveyableObject::getObjectID()
	 */
	public function getObjectID() {
		return 0;
	}

	/**
	 * @see UserNotificationConveyableObject::getTitle()
	 */
	public function getTitle() {
		return '';
	}

	/**
	 * @see UserNotificationConveyableObject::getURL()
	 */
	public function getURL() {
		return '';
	}
}
?>
