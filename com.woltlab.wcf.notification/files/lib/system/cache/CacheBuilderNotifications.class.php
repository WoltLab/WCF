<?php
require_once(WCF_DIR.'lib/system/cache/CacheBuilder.class.php');

/**
 * Caches notifications related data
 *
 * @author	Oliver Kliebisch
 * @copyright	2009-2010 Oliver Kliebisch
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.wcf.user.notification
 * @subpackage	system.cache
 * @category 	Community Framework
 */
class CacheBuilderNotifications implements CacheBuilder {
	/**
	 * @see CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		list($cache, $packageID) = explode('-', $cacheResource['cache']);

		// initialize arrays
		$data = array();
		$data['objectTypes'] = array();
		$data['events'] = array();
		$data['notificationTypes'] = array();                

		/* object types */
		// get type ids
		$typeIDArray = array();
		$sql = "SELECT		objectType, objectTypeID
			FROM		wcf".WCF_N."_user_notification_object_type object_type,
					wcf".WCF_N."_package_dependency package_dependency
			WHERE 		object_type.packageID = package_dependency.dependency
					AND package_dependency.packageID = ".$packageID."
			ORDER BY	package_dependency.priority";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$typeIDArray[$row['objectType']] = $row['objectTypeID'];
		}

		if (count($typeIDArray) > 0) {
			$sql = "SELECT		object_type.*, package.packageDir
				FROM		wcf".WCF_N."_user_notification_object_type object_type
				LEFT JOIN	wcf".WCF_N."_package package
				ON		(package.packageID = object_type.packageID)
				WHERE		object_type.objectTypeID IN (".implode(',', $typeIDArray).")";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$row['className'] = StringUtil::getClassName($row['classFile']);
				$data['objectTypes'][] = $row;
			}
		}

		/* events */
		// get event ids
		$eventIDArray = array();
		$sql = "SELECT		eventName, eventID
			FROM		wcf".WCF_N."_user_notification_event event,
					wcf".WCF_N."_package_dependency package_dependency
			WHERE 		event.packageID = package_dependency.dependency
					AND package_dependency.packageID = ".$packageID."
			ORDER BY	package_dependency.priority";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$eventIDArray[$row['eventName']] = $row['eventID'];
		}

		if (count($eventIDArray) > 0) {
			$sql = "SELECT		event.*, package.packageDir
				FROM		wcf".WCF_N."_user_notification_event event
				LEFT JOIN	wcf".WCF_N."_package package
				ON		(package.packageID = event.packageID)
				WHERE		event.eventID IN (".implode(',', $eventIDArray).")";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$row['className'] = StringUtil::getClassName($row['classFile']);
				$data['events'][] = $row;                                
			}
		}

		/* notification types */
		// get notification type ids
		$notificationTypeIDArray = array();
		$sql = "SELECT		notificationType, notificationTypeID
			FROM		wcf".WCF_N."_user_notification_type notification_type,
					wcf".WCF_N."_package_dependency package_dependency
			WHERE 		notification_type.packageID = package_dependency.dependency
					AND package_dependency.packageID = ".$packageID."
			ORDER BY	package_dependency.priority";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$notificationTypeIDArray[$row['notificationType']] = $row['notificationTypeID'];
		}

		if (count($notificationTypeIDArray) > 0) {
			$sql = "SELECT		notification_type.*, package.packageDir
				FROM		wcf".WCF_N."_user_notification_type notification_type
				LEFT JOIN	wcf".WCF_N."_package package
				ON		(package.packageID = notification_type.packageID)
				WHERE		notification_type.notificationTypeID IN (".implode(',', $notificationTypeIDArray).")";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$row['className'] = StringUtil::getClassName($row['classFile']);
				$data['notificationTypes'][] = $row;
			}
		}

		return $data;
	}
}
?>