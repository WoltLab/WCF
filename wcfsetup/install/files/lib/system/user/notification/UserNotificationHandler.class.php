<?php
namespace wcf\system\user\notification;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\notification\event\recipient\UserNotificationEventRecipientList;
use wcf\data\user\notification\event\UserNotificationEventList;
use wcf\data\user\notification\UserNotification;
use wcf\data\user\notification\UserNotificationAction;
use wcf\data\user\notification\UserNotificationList;
use wcf\data\user\User;
use wcf\data\user\UserEditor;
use wcf\data\user\UserProfile;
use wcf\system\cache\builder\UserNotificationEventCacheBuilder;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\mail\Mail;
use wcf\system\user\notification\event\IUserNotificationEvent;
use wcf\system\user\notification\object\IUserNotificationObject;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Handles user notifications.
 * 
 * @author	Marcel Werk, Oliver Kliebisch
 * @copyright	2001-2013 WoltLab GmbH, Oliver Kliebisch
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.user.notification
 * @category	Community Framework
 */
class UserNotificationHandler extends SingletonFactory {
	/**
	 * list of available object types
	 * @var	array
	 */
	protected $availableObjectTypes = array();
	
	/**
	 * list of available events
	 * @var	array
	 */
	protected $availableEvents = array();
	
	/**
	 * number of outstanding notifications
	 * @var	integer
	 */
	protected $notificationCount = null;
	
	/**
	 * list of object types
	 * @var	array<wcf\data\object\type\ObjectType>
	 */
	protected $objectTypes = array();
	
	/**
	 * @see	wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		// get available object types
		$this->objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.notification.objectType');
		foreach ($this->objectTypes as $typeName => $object) {
			$this->availableObjectTypes[$typeName] = $object->getProcessor();
		}
		
		// get available events
		$this->availableEvents = UserNotificationEventCacheBuilder::getInstance()->getData();
	}
	
	/**
	 * Triggers a notification event.
	 * 
	 * @param	string								$eventName
	 * @param	string								$objectType
	 * @param	wcf\system\user\notification\object\IUserNotificationObject	$notificationObject
	 * @param	array<integer>							$recipientIDs
	 * @param	array<mixed>							$additionalData
	 */
	public function fireEvent($eventName, $objectType, IUserNotificationObject $notificationObject, array $recipientIDs, array $additionalData = array()) {
		// check given object type and event name
		if (!isset($this->availableEvents[$objectType][$eventName])) {
			throw new SystemException("Unknown event ".$objectType."-".$eventName." given");
		}
		
		// get objects
		$objectTypeObject = $this->availableObjectTypes[$objectType];
		$event = $this->availableEvents[$objectType][$eventName];
		// set object data
		$event->setObject(new UserNotification(null, array()), $notificationObject, new UserProfile(WCF::getUser()), $additionalData);
		
		// find existing events
		$userIDs = array();
		$conditionBuilder = new PreparedStatementConditionBuilder();
		$conditionBuilder->add('notification_to_user.notificationID = notification.notificationID');
		$conditionBuilder->add('notification_to_user.userID IN (?)', array($recipientIDs));
		$conditionBuilder->add('notification.eventHash = ?', array($event->getEventHash()));
		$sql = "SELECT	notification_to_user.userID
			FROM	wcf".WCF_N."_user_notification notification,
				wcf".WCF_N."_user_notification_to_user notification_to_user
			".$conditionBuilder;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditionBuilder->getParameters());
		while ($row = $statement->fetchArray()) $userIDs[] = $row['userID'];
		
		// skip recipients with outstanding notifications
		if (!empty($userIDs)) {
			$recipientIDs = array_diff($recipientIDs, $userIDs);
			if (empty($recipientIDs)) return;
		}
		
		// get recipients
		$recipientList = new UserNotificationEventRecipientList();
		$recipientList->getConditionBuilder()->add('event_to_user.eventID = ?', array($event->eventID));
		$recipientList->getConditionBuilder()->add('event_to_user.userID IN (?)', array($recipientIDs));
		$recipientList->readObjects();
		if (count($recipientList)) {
			// find existing notification
			$notification = UserNotification::getNotification($objectTypeObject->packageID, $event->eventID, $notificationObject->getObjectID());
			if ($notification !== null) {
				// only update recipients
				$action = new UserNotificationAction(array($notification), 'addRecipients', array(
					'recipients' => $recipientList->getObjects()
				));
				$action->executeAction();
			}
			else {
				// create new notification
				$action = new UserNotificationAction(array(), 'create', array(
					'data' => array(
						'packageID' => $objectTypeObject->packageID,
						'eventID' => $event->eventID,
						'objectID' => $notificationObject->getObjectID(),
						'authorID' => ($event->getAuthorID() ?: null),
						'time' => TIME_NOW,
						'eventHash' => $event->getEventHash(),
						'additionalData' => serialize($additionalData)
					),
					'recipients' => $recipientList->getObjects()
				));
				$result = $action->executeAction();
				$notification = $result['returnValues'];
			}
			
			// sends notifications
			foreach ($recipientList->getObjects() as $recipient) {
				if ($recipient->mailNotificationType == 'instant') {
					$this->sendInstantMailNotification($notification, $recipient, $event);
				}
			}
			
			// reset notification count
			UserStorageHandler::getInstance()->reset($recipientList->getObjectIDs(), 'userNotificationCount');
		}
	}
	
	/**
	 * Returns the number of outstanding notifications for the active user.
	 * 
	 * @return	integer
	 */
	public function getNotificationCount() {
		if ($this->notificationCount === null) {
			$this->notificationCount = 0;
			
			if (WCF::getUser()->userID) {
				// load storage data
				UserStorageHandler::getInstance()->loadStorage(array(WCF::getUser()->userID));
					
				// get ids
				$data = UserStorageHandler::getInstance()->getStorage(array(WCF::getUser()->userID), 'userNotificationCount');
				
				// cache does not exist or is outdated
				if ($data[WCF::getUser()->userID] === null) {
					$conditionBuilder = new PreparedStatementConditionBuilder();
					$conditionBuilder->add('notification.notificationID = notification_to_user.notificationID');
					$conditionBuilder->add('notification_to_user.userID = ?', array(WCF::getUser()->userID));
					
					$sql = "SELECT	COUNT(*) AS count
						FROM	wcf".WCF_N."_user_notification_to_user notification_to_user,
							wcf".WCF_N."_user_notification notification
						".$conditionBuilder->__toString();
					$statement = WCF::getDB()->prepareStatement($sql);
					$statement->execute($conditionBuilder->getParameters());
					$row = $statement->fetchArray();
					$this->notificationCount = $row['count'];
					
					// update storage data
					UserStorageHandler::getInstance()->update(WCF::getUser()->userID, 'userNotificationCount', serialize($this->notificationCount));
				}
				else {
					$this->notificationCount = unserialize($data[WCF::getUser()->userID]);
				}
			}
		}
		
		return $this->notificationCount;
	}
	
	/**
	 * Returns a limited list of outstanding notifications.
	 * 
	 * @param	integer		$limit
	 * @param	integer		$offset
	 * @return	array<array>
	 */
	public function getNotifications($limit = 5, $offset = 0) {
		// build enormous query
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("notification_to_user.userID = ?", array(WCF::getUser()->userID));
		$conditions->add("notification.notificationID = notification_to_user.notificationID");
		
		$sql = "SELECT		notification_to_user.notificationID, notification_event.eventID,
					object_type.objectType, notification.objectID,
					notification.additionalData, notification.authorID,
					notification.time
			FROM		wcf".WCF_N."_user_notification_to_user notification_to_user,
					wcf".WCF_N."_user_notification notification
			LEFT JOIN	wcf".WCF_N."_user_notification_event notification_event
			ON		(notification_event.eventID = notification.eventID)
			LEFT JOIN	wcf".WCF_N."_object_type object_type
			ON		(object_type.objectTypeID = notification_event.objectTypeID)
			".$conditions."
			ORDER BY	notification.time DESC";
		$statement = WCF::getDB()->prepareStatement($sql, $limit, $offset);
		$statement->execute($conditions->getParameters());
		
		$authorIDs = $events = $objectTypes = $eventIDs = $notificationIDs = array();
		while ($row = $statement->fetchArray()) {
			$events[] = $row;
			
			// cache object types
			if (!isset($objectTypes[$row['objectType']])) {
				$objectTypes[$row['objectType']] = array(
					'objectType' => $this->availableObjectTypes[$row['objectType']],
					'objectIDs' => array(),
					'objects' => array()
				);
			}
			
			$objectTypes[$row['objectType']]['objectIDs'][] = $row['objectID'];
			$eventIDs[] = $row['eventID'];
			$notificationIDs[] = $row['notificationID'];
			$authorIDs[] = $row['authorID'];
		}
		
		// return an empty set if no notifications exist
		if (empty($events)) {
			return array(
				'count' => 0,
				'notifications' => array()
			);
		}
		
		// load authors
		$authors = UserProfile::getUserProfiles($authorIDs);
		$unknownAuthor = new UserProfile(new User(null, array('userID' => null, 'username' => WCF::getLanguage()->get('wcf.user.guest'))));
		
		// load objects associated with each object type
		foreach ($objectTypes as $objectType => $objectData) {
			$objectTypes[$objectType]['objects'] = $objectData['objectType']->getObjectsByIDs($objectData['objectIDs']);
		}
		
		// load required events
		$eventList = new UserNotificationEventList();
		$eventList->getConditionBuilder()->add("user_notification_event.eventID IN (?)", array($eventIDs));
		$eventList->readObjects();
		$eventObjects = $eventList->getObjects();
		
		// load notification objects
		$notificationList = new UserNotificationList();
		$notificationList->getConditionBuilder()->add("user_notification.notificationID IN (?)", array($notificationIDs));
		$notificationList->readObjects();
		$notificationObjects = $notificationList->getObjects();
		
		// build notification data
		$notifications = array();
		foreach ($events as $event) {
			$className = $eventObjects[$event['eventID']]->className;
			$class = new $className($eventObjects[$event['eventID']]);
			
			$class->setObject(
				$notificationObjects[$event['notificationID']],
				$objectTypes[$event['objectType']]['objects'][$event['objectID']],
				(isset($authors[$event['authorID']]) ? $authors[$event['authorID']] : $unknownAuthor),
				unserialize($event['additionalData'])
			);
			
			$data = array(
				'event' => $class,
				'notificationID' => $event['notificationID'],
				'time' => $event['time']
			);
			
			$notifications[] = $data;
		}
		
		return array(
			'count' => count($notifications),
			'notifications' => $notifications
		);
	}
	
	/**
	 * Returns event object for given object type and event, returns NULL on failure.
	 * 
	 * @param	string		$objectType
	 * @param	string		$eventName
	 * @return	wcf\system\user\notification\event\IUserNotificationEvent
	 */
	public function getEvent($objectType, $eventName) {
		if (!isset($this->availableEvents[$objectType][$eventName])) return null;
		
		return $this->availableEvents[$objectType][$eventName];
	}
	
	/**
	 * Retrieves a notification id.
	 * 
	 * @param	integer		$eventID
	 * @param	integer		$objectID
	 * @param	integer		$authorID
	 * @param	integer		$time
	 * @return	integer
	 */
	public function getNotificationID($eventID, $objectID, $authorID = null, $time = null) {
		if ($authorID === null && $time === null) {
			throw new SystemException("authorID and time cannot be omitted at once.");
		}
		
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("eventID = ?", array($eventID));
		$conditions->add("objectID = ?", array($objectID));
		if ($authorID !== null) $conditions->add("authorID = ?", array($authorID));
		if ($time !== null) $conditions->add("time = ?", array($time));
		
		$sql = "SELECT	notificationID
			FROM	wcf".WCF_N."_user_notification
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		
		$row = $statement->fetchArray();
		
		return ($row === false) ? null : $row['notificationID'];
	}
	
	/**
	 * Returns a list of available object types.
	 * 
	 * @return	array<wcf\system\user\notification\object\type\IUserNotificationObjectType>
	 */
	public function getAvailableObjectTypes() {
		return $this->availableObjectTypes;
	}
	
	/**
	 * Returns a list of available events.
	 * 
	 * @return	array<wcf\system\user\notification\event\IUserNotificationEvent>
	 */
	public function getAvailableEvents() {
		return $this->availableEvents;
	}
	
	/**
	 * Returns object type id by name.
	 * 
	 * @param	string		$objectType
	 * @return	integer
	 */
	public function getObjectTypeID($objectType) {
		if (isset($this->objectTypes[$objectType])) {
			return $this->objectTypes[$objectType]->objectTypeID;
		}
		
		return 0;
	}
	
	/**
	 * Returns object type by name.
	 * 
	 * @param	string		$objectType
	 * @return	object
	 */
	public function getObjectTypeProcessor($objectType) {
		if (isset($this->availableObjectTypes[$objectType])) {
			return $this->availableObjectTypes[$objectType];
		}
		
		return null;
	}
	
	/**
	 * Sends the mail notification.
	 * 
	 * @param	wcf\data\user\notification\UserNotification			$notification
	 * @param	wcf\data\user\User						$user
	 * @param	wcf\system\user\notification\event\IUserNotificationEvent	$event
	 */
	public function sendInstantMailNotification(UserNotification $notification, User $user, IUserNotificationEvent $event) {
		// recipient's language
		$event->setLanguage($user->getLanguage());
		
		// add mail header
		$message = $user->getLanguage()->getDynamicVariable('wcf.user.notification.mail.header', array(
			'user' => $user
		))."\n\n";
		
		// get message
		$message .= $event->getEmailMessage();
		
		// append notification mail footer
		$token = $user->notificationMailToken;
		if (!$token) {
			// generate token if not present
			$token = mb_substr(StringUtil::getHash(serialize(array($user->userID, StringUtil::getRandomID()))), 0, 20);
			$editor = new UserEditor($user);
			$editor->update(array('notificationMailToken' => $token));
		}
		$message .= "\n\n".$user->getLanguage()->getDynamicVariable('wcf.user.notification.mail.footer', array(
			'user' => $user,
			'token' => $token,
			'notification' => $notification
		));
		
		// build mail
		$mail = new Mail(array($user->username => $user->email), $user->getLanguage()->getDynamicVariable('wcf.user.notification.mail.subject', array('title' => $event->getEmailTitle())), $message);
		$mail->setLanguage($user->getLanguage());
		$mail->send();
	}
	
	/**
	 * Deletes notifications.
	 * 
	 * @param	string		$eventName
	 * @param	string		$objectType
	 * @param	array<integer>	$recipientIDs
	 * @param	array<integer>	$objectIDs
	 */
	public function deleteNotifications($eventName, $objectType, array $recipientIDs, array $objectIDs = array()) {
		// check given object type and event name
		if (!isset($this->availableEvents[$objectType][$eventName])) {
			throw new SystemException("Unknown event ".$objectType."-".$eventName." given");
		}
		
		// get objects
		$objectTypeObject = $this->availableObjectTypes[$objectType];
		$event = $this->availableEvents[$objectType][$eventName];
		
		// delete notifications
		$sql = "DELETE FROM	wcf".WCF_N."_user_notification_to_user
			WHERE		notificationID IN (
						SELECT	notificationID
						FROM	wcf".WCF_N."_user_notification
						WHERE	packageID = ?
							AND eventID = ?
							".(!empty($objectIDs) ? "AND objectID IN (?".(count($objectIDs) > 1 ? str_repeat(',?', count($objectIDs) - 1) : '').")" : '')."	
					)
					".(!empty($recipientIDs) ? ("AND userID IN (?".(count($recipientIDs) > 1 ? str_repeat(',?', count($recipientIDs) - 1) : '').")") : '');
		$parameters = array($objectTypeObject->packageID, $event->eventID);
		if (!empty($objectIDs)) $parameters = array_merge($parameters, $objectIDs);
		if (!empty($recipientIDs)) $parameters = array_merge($parameters, $recipientIDs);
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($parameters);
		
		// reset storage
		if (!empty($recipientIDs)) {
			UserStorageHandler::getInstance()->reset($recipientIDs, 'userNotificationCount');
		}
		else {
			UserStorageHandler::getInstance()->resetAll('userNotificationCount');
		}
	}
	
	/**
	 * Returns the user's notification setting for the given event.
	 * 
	 * @param	string		$objectType
	 * @param	string		$eventName
	 * @return	mixed
	 */
	public function getEventSetting($objectType, $eventName) {
		// get event
		$event = $this->getEvent($objectType, $eventName);
		
		// get setting
		$sql = "SELECT	mailNotificationType
			FROM	wcf".WCF_N."_user_notification_event_to_user
			WHERE	eventID = ?
				AND userID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($event->eventID, WCF::getUser()->userID));
		$row = $statement->fetchArray();
		if ($row === false) return false;
		return $row['mailNotificationType'];
	}
}
