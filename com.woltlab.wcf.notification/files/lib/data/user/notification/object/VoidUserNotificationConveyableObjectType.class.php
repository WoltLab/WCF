<?php
// wcf imports
require_once(WCF_DIR.'lib/data/user/notification/object/AbstractUserNotificationConveyableObjectType.class.php');
require_once(WCF_DIR.'lib/data/user/notification/object/VoidUserNotificationConveyableObject.class.php');

/**
 * A notification object type for general unbound notifications
 *
 * @author	Marcel Werk, Oliver Kliebisch
 * @copyright	2009-2011 WoltLab GmbH, Oliver Kliebisch
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.user.notification
 * @subpackage	data.user.notification.object
 * @category 	Community Framework
 */
class VoidUserNotificationConveyableObjectType extends AbstractUserNotificationConveyableObjectType {
	/**
	 * @see UserNotificationConveyableObjectType::getObjectByID()
	 */
	public function getObjectByID($objectID) {
	       return new VoidUserNotificationConveyableObject();
	}

	/**
	 * @see UserNotificationConveyableObjectType::getObjectByObject()
	 */
	public function getObjectByObject($object) {
		return new VoidUserNotificationConveyableObject();
	}

	/**
	 * @see UserNotificationConveyableObjectType::getObjectsByIDs()
	 */
	public function getObjectsByIDs(array $objectIDs) {
		return array(0 => new VoidUserNotificationConveyableObject());
	}

	/**
	 * @see UserNotificationConveyableObjectType::getPackageID()
	 */
	public function getPackageID() {                
		// void notifications are bound directly to the notification package
		return WCF::getPackageID('com.woltlab.wcf.user.notification');
	}

	/**
	 * @see UserNotificationConveyableObjectType::getObjects()
	 */
	public function getObjects($data) {
		return array(0 => new VoidUserNotificationConveyableObject());
	}
}
?>
