<?php
// wcf imports
require_once(WCF_DIR.'lib/data/user/NotificationUser.class.php');
require_once(WCF_DIR.'lib/data/user/notification/UserNotificationEditor.class.php');

/**
 * The core class of the notification system
 *
 * @author	Marcel Werk, Tim Duesterhus, Oliver Kliebisch
 * @copyright	2009-2011 Oliver Kliebisch
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.user.notification
 * @subpackage	data.user.notification
 * @category 	Community Framework
 */
class UserNotificationHandler {
	/**
	 * list of available notification object types
	 * @var	array<UserNotificationObjectType>
	 */
	public static $availableNotificationObjectTypes = null;

	/**
	 * list of available notification types
	 * @var	array<UserNotificationType>
	 */
	public static $availableNotificationTypes = null;

	/**
	 * available notification object package IDs
	 * @var	array<integer>
	 */
	protected static $availableNotificationObjectIDs = null;

	/**
	 * notification cache data
	 * @var array<mixed>
	 */
	protected static $cacheData = null;

	/**
	 * The reference user object for the permission check
	 * @var User
	 */
	protected static $user = null;

	/**
	 * Triggers a notification event
	 *
	 * @param       string          $eventName
	 * @param       string          $objectType
	 * @param       mixed           $notificationObject
	 * @param       integer         $recipientUserID
	 * @param       array<mixed>    $additionalData
	 */
	public static function fireEvent($eventName, $objectType, $notificationObject, $recipientUserID, $additionalData = array()) {
		if (!MODULE_USER_NOTIFICATION) return;
		// get recipient and apply it to the handler
		$recipient = new NotificationUser($recipientUserID);
		if ($recipient->userID == 0) return;
		self::setUser($recipient);

		$objectTypes = self::getAvailableNotificationObjectTypes();
		if (isset($objectTypes[$objectType]['events'][$eventName])) {
			$objectTypeObject = $objectTypes[$objectType]['object'];
			$eventObject = $objectTypes[$objectType]['events'][$eventName];

			// save memory
			unset ($objectTypes);

			// fetches the notification object if $notificationObject isn't already one
			if (!$notificationObject instanceof UserNotificationConveyableObject) {
				$notificationObject = $objectTypeObject->getObjects($notificationObject);
				$notificationObject = current($notificationObject);
			}

			if (!$notificationObject) {
				throw new SystemException("Unable to fetch the notification object with the type '".$objectType."'", 11000);
			}
			$eventObject->setObject($notificationObject, $additionalData);

			// get notification types
			$notificationTypes = $recipient->getEventNotificationTypes($eventName, $objectType);

			// switch to recipient language
			// enable recipient language
			$languages = array(0 => WCF::getLanguage(), WCF::getLanguage()->getLanguageID() => WCF::getLanguage());
			if (!isset($languages[$recipient->languageID])) $languages[$recipient->languageID] = new Language($recipient->languageID);
			$languages[$recipient->languageID]->setLocale();
			$eventObject->setLanguage($languages[$recipient->languageID]);

			// prepare messages and send notification messages
			$notification = UserNotificationEditor::create(array(
				'userID' => $recipient->userID,
				'packageID' => $objectTypeObject->getPackageID(),
				'objectType' => $objectType,
				'objectID' => $notificationObject->getObjectID(),
				'eventName' => $eventName,
				'time' => TIME_NOW,
				'shortOutput' => $eventObject->getShortOutput($eventName),
				'mediumOutput' => $eventObject->getMediumOutput($eventName),
				'longOutput' => $eventObject->getOutput($eventName),
				'additionalData' => $additionalData
			));
			
			foreach ($notificationTypes as $notificationType) {
				if ($eventObject->supportsNotificationType($notificationType) == false) {
					continue;
				}

				$notificationType->send($recipient, $eventObject, $notification);
			}

			// add notification flag
			$recipient->addOutstandingNotification(self::getNotificationObjectTypeObject($notification->objectType)->getPackageID());

			// enable user language
			WCF::getLanguage()->setLocale();
		}
	}

	/**
	 * Revokes an event and all its messages if possible
	 *
	 * @param       array<string>   $eventName
	 * @param       string          $objectType
	 * @param       mixed           $notificationObject
	 * @param       array<mixed>    $additionalData
	 */
	public static function revokeEvent($eventNames, $objectType, $notificationObjects, $additionalData = array()) {
		$objectTypes = self::getAvailableNotificationObjectTypes();
		$objectTypeObject = $objectTypes[$objectType]['object'];
		$eventObjectArray = array();
		foreach ($eventNames as $key => $eventName) {
			if (isset($objectTypes[$objectType]['events'][$eventName])) {
				$eventObjectArray[$eventName] = $objectTypes[$objectType]['events'][$eventName];
			}
			else {
				unset($eventNames[$key]);
			}
		}

		unset($objectTypes);

		// fetches the notification objects
		$notificationObjects = $objectTypeObject->getObjects($notificationObjects);

		if (!count($notificationObjects)) {
			throw new SystemException("Unable to fetch the notification object with the type '".$objectType."'", 11000);
		}
		$objectIDArray = array();
		foreach ($notificationObjects as $notificationObject) {
			$objectIDArray[] = $notificationObject->getObjectID();
		}

		$sql = "SELECT  n.*,
				GROUP_CONCAT(u.userID SEPARATOR ',') AS userID
			FROM    wcf".WCF_N."_user_notification n
			LEFT JOIN
				wcf".WCF_N."_user_notification_to_user u
				ON n.notificationID = u.notificationID
			WHERE	objectType = '".escapeString($objectType)."'
			AND     packageID = ".$objectTypeObject->getPackageID()."
			AND     objectID IN(".implode(',', $objectIDArray).")
			AND     eventName IN('".implode("','", array_map('escapeString', $eventNames))."')";
		$result = WCF::getDB()->getResultList($sql);
		if ($result[0]['notificationID'] === null) return;
		$affectedUserIDs = array();
		$notificationIDArray = array();
		foreach ($result as $row) {
			$notification = new UserNotificationEditor(null, $row);
			$notificationIDArray[] = $notification->notificationID;
			$row['userID'] = explode(',', $row['userID']);
			$recipients = array();
			foreach ($row['userID'] as $userID) {
				// could we do it better?
				$recipients[] = new UserNotificationUser($userID, null, false);
			}

			$eventObject = $eventObjectArray[$notification->eventName];
			$notificationObject = $notificationObjects[$notification->objectID];
			$eventObject->setObject($notificationObject, $additionalData);
			foreach ($recipients as $recipient) {
				$notificationTypeArray = UserNotificationHandler::getAvailableNotificationObjectTypes();
				foreach ($notificationTypeArray as $notificationType) {
					$notificationTypeObject = null;
					try {
						$notificationTypeObject = self::getNotificationTypeObject($notificationType);
						if (!$notificationTypeObject) continue;
					}
					catch (SystemException $ex) {
						// notification object might be missing but that would be no error
						continue;
					}
					// revoke messages if supported
					$notificationTypeObject->revoke($recipient, $eventObject, $notification);
				}

				$affectedUserIDs[] = $recipient->userID;
			}
		}

		// delete notification data
		UserNotificationEditor::deleteAll($notificationIDArray);

		// update user flags
		UserNotificationUser::recalculateUserNotificationFlags($affectedUserIDs);
	}

	/**
	 * Gets notification objects by their ids.
	 *
	 * @param	string		$objectType
	 * @param	mixed		$objectID
	 * @return	mixed
	 */
	public static function getNotificationObjectByID($objectType, $objectID) {
		// get notification object type object
		$typeObject = null;
		try {
			$typeObject = self::getNotificationObjectTypeObject($objectType);
		}
		catch (SystemException $e) {
			return null;
		}

		// get notification objects
		return $typeObject->getObjectByID($objectID);
	}

	/**
	 * Returns the object of a notification object type.
	 *
	 * @param	string		$objectType
	 * @return	NotificationObjectType
	 */
	public static function getNotificationObjectTypeObject($objectType) {
		$types = self::getAvailableNotificationObjectTypes();
		if (!isset($types[$objectType])) {
			throw new SystemException("Unknown notification object type '".$objectType."'", 11000);
		}

		return $types[$objectType]['object'];
	}

	/**
	 * Returns the object of a notification type.
	 *
	 * @param	string		$notificationType
	 * @return	NotificationType
	 */
	public static function getNotificationTypeObject($notificationType) {
		$types = self::getAvailableNotificationTypes();
		if (!isset($types[$notificationType])) {
			throw new SystemException("Unknown notification object type '".$notificationType."'", 11000);
		}

		return $types[$notificationType];
	}

	/**
	 * Returns a list of available notification object types.
	 *
	 * @return	array<NotificationObjectType>
	 */
	public static function getAvailableNotificationObjectTypes() {
		if (self::$availableNotificationObjectTypes === null) {
			self::loadCache();
			$types = self::$cacheData['objectTypes'];
			foreach ($types as $type) {
				// check options and modules
				if (!empty($type['options'])) {
					$options = explode(',', StringUtil::toUpperCase($type['options']));
					foreach ($options as $option) {
						if (!defined($option) || !constant($option))
								continue 2;
					}
				}

				// check permissions
				if (!empty($type['permissions'])) {
					$permissions = explode(',', $type['permissions']);
					foreach ($permissions as $permission) {
						if (!self::getUser()->getPermission($permission))
								continue 2;
					}
				}

				// get path to class file
				if (empty($type['packageDir'])) {
					$path = WCF_DIR;
				}
				else {
					$path = FileUtil::getRealPath(WCF_DIR.$type['packageDir']);
				}
				$path .= $type['classFile'];

				// include class file
				if (!class_exists($type['className'])) {
					if (!file_exists($path)) {
						throw new SystemException("Unable to find class file '".$path."'", 11000);
					}
					require_once($path);
				}

				// instance object
				if (!class_exists($type['className'])) {
					throw new SystemException("Unable to find class '".$type['className']."'", 11001);
				}
				self::$availableNotificationObjectTypes[$type['objectType']]['object'] = new $type['className'];
			}

			$events = self::$cacheData['events'];
			foreach ($events as $event) {
				// check options and modules
				if (!empty($event['options'])) {
					$options = explode(',', StringUtil::toUpperCase($event['options']));
					foreach ($options as $option) {
						if (!defined($option) || !constant($option))
								continue 2;
					}
				}

				// check permissions
				if (!empty($event['permissions'])) {
					$permissions = explode(',', $event['permissions']);
					foreach ($permissions as $permission) {
						if (!self::getUser()->getPermission($permission))
								continue 2;
					}
				}

				// get path to class file
				if (empty($event['packageDir'])) {
					$path = WCF_DIR;
				}
				else {
					$path = FileUtil::getRealPath(WCF_DIR.$event['packageDir']);
				}
				$path .= $event['classFile'];

				// include class file
				if (!class_exists($event['className'])) {
					if (!file_exists($path)) {
						// load default event
						require_once(WCF_DIR.'lib/data/user/notification/event/DefaultNotificationEvent.class.php');
						$event['className'] = 'DefaultNotificationEvent';
						//throw new SystemException("Unable to find class file '".$path."'", 11000);
					}
					else require_once($path);
				}

				// instance object
				if (!class_exists($event['className'])) {
					throw new SystemException("Unable to find class '".$event['className']."'", 11001);
				}
				self::$availableNotificationObjectTypes[$event['objectType']]['events'][$event['eventName']] = new $event['className']($event);
			}
		}

		return self::$availableNotificationObjectTypes;
	}

	/**
	 * Returns a list of available notification types
	 *
	 * @return	array<NotificationType>
	 */
	public static function getAvailableNotificationTypes() {
		if (self::$availableNotificationTypes === null) {
			self::loadCache();
			$types = self::$cacheData['notificationTypes'];
			foreach ($types as $type) {
				// check options and modules
				if (!empty($type['options'])) {
					$options = explode(',', StringUtil::toUpperCase($type['options']));
					foreach ($options as $option) {
						if (!defined($option) || !constant($option))
								continue 2;
					}
				}

				// check permissions
				if (!empty($type['permissions'])) {
					$permissions = explode(',', $type['permissions']);
					foreach ($permissions as $permission) {
						if (!self::getUser()->getPermission($permission))
								continue 2;
					}
				}

				// get path to class file
				if (empty($type['packageDir'])) {
					$path = WCF_DIR;
				}
				else {
					$path = FileUtil::getRealPath(WCF_DIR.$type['packageDir']);
				}
				$path .= $type['classFile'];

				// include class file
				if (!class_exists($type['className'])) {
					if (!file_exists($path)) {
						throw new SystemException("Unable to find class file '".$path."'", 11000);
					}
					require_once($path);
				}

				// instance object
				if (!class_exists($type['className'])) {
					throw new SystemException("Unable to find class '".$type['className']."'", 11001);
				}
				self::$availableNotificationTypes[$type['notificationType']] = new $type['className'];
			}
		}

		return self::$availableNotificationTypes;
	}

	/**
	 * Returns a concatenized string of available package IDs
	 *
	 * @return string
	 */
	public static function getAvailablePackageIDs() {
		if (self::$availableNotificationObjectIDs === null) {
			$objectTypeObjects = self::getAvailableNotificationObjectTypes();

			self::$availableNotificationObjectIDs = array();
			if ($objectTypeObjects) {
				foreach ($objectTypeObjects as $objectType) {
					if ($packageID = $objectType['object']->getPackageID()) {
						self::$availableNotificationObjectIDs[] = $packageID;
					}

					$additionalPackageIDs = $objectType['object']->getAdditionalPackageIDs();
					if (!empty($additionalPackageIDs)) {
						self::$availableNotificationObjectIDs = array_merge($additionalPackageIDs, self::$availableNotificationObjectIDs);
					}
				}
			}

			array_unique(self::$availableNotificationObjectIDs);
		}

		return count(self::$availableNotificationObjectIDs) ? '0,'.implode(',', self::$availableNotificationObjectIDs) : '0';
	}

	/**
	 * Sets the user for permission checks
	 *
	 * @param UserSession $user
	 */
	public static function setUser(UserSession $user) {
		self::$user = $user;
		self::$availableNotificationObjectTypes = self::$availableNotificationTypes = self::$availableNotificationObjectIDs = null;
	}

	/**
	 * Returns the user for permission checks
	 *
	 * @return UserSession
	 */
	public static function getUser() {
		if (self::$user === null || !self::$user instanceof UserSession) {
			self::$user = WCF::getUser();
		}

		return self::$user;
	}

	/**
	 * Loads the notification cache
	 */
	protected static function loadCache() {
		if (self::$cacheData === null) {
			WCF::getCache()->addResource('notifications-'.PACKAGE_ID, WCF_DIR.'cache/cache.notifications-'.PACKAGE_ID.'.php', WCF_DIR.'lib/system/cache/CacheBuilderNotifications.class.php');
			self::$cacheData = WCF::getCache()->get('notifications-'.PACKAGE_ID);
		}
	}
}
?>