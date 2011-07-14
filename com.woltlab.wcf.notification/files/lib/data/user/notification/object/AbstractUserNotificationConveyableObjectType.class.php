<?php
// wcf imports
require_once(WCF_DIR.'lib/data/user/notification/object/UserNotificationConveyableObjectType.class.php');

/**
 * A default implementation for NotificationObjectType to provide access to NotificationObjects
 *
 * @author	Oliver Kliebisch
 * @copyright	2009-2010 Oliver Kliebisch
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.user.notification
 * @subpackage	data.user.notification.object
 * @category 	Community Framework
 */
abstract class AbstractUserNotificationConveyableObjectType implements UserNotificationConveyableObjectType {
	/**
	 * @see NotficiationObjectType::getObjects()
	 */
	public function getObjects($data) {
		$objectArray = array();
		if (is_int($data) || is_string($data)) {
			$object = $this->getObjectByID($data);
			if ($object) $objectArray[$object->getObjectID()] = $object;
		}
		else if (is_array($data)) {
			$objectArray = $this->getObjectsByIDArray($data);
		}
		else if (is_object($data)) {
			$object = $this->getObjectByObject($data);
			if ($object) $objectArray[$object->getObjectID()] = $object;
		}

		return $objectArray;
	}

	/**
	 * @see NotficiationObjectType::getAdditionalPackageIDs()
	 */
	public function getAdditionalPackageIDs() {
		return array();
	}
}
?>