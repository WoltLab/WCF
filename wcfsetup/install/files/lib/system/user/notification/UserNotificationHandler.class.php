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
 * @copyright	2001-2014 WoltLab GmbH, Oliver Kliebisch
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
	 * @var	array<\wcf\data\object\type\ObjectType>
	 */
	protected $objectTypes = array();
	
	/**
	 * @see	\wcf\system\SingletonFactory::init()
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
	 * @param	\wcf\system\user\notification\object\IUserNotificationObject	$notificationObject
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
		
		// get author's profile
		$userProfile = null;
		if ($notificationObject->getAuthorID()) {
			if ($notificationObject->getAuthorID() == WCF::getUser()->userID) {
				$userProfile = new UserProfile(WCF::getUser());
			}
			else {
				$userProfile = UserProfile::getUserProfile($notificationObject->getAuthorID());
			}
		}
		if ($userProfile === null) {
			$userProfile = new UserProfile(new User(null, array()));
		}
		
		// set object data
		$event->setObject(new UserNotification(null, array()), $notificationObject, $userProfile, $additionalData);
		
		// find existing notifications
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("userID IN (?)", array($recipientIDs));
		$conditions->add("eventID = ?", array($event->eventID));
		$conditions->add("objectID = ?", array($notificationObject->getObjectID()));
		$conditions->add("confirmed = ?", array(0));
		
		$sql = "SELECT	notificationID, userID
			FROM	wcf".WCF_N."_user_notification
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		$notifications = array();
		while ($row = $statement->fetchArray()) {
			$notifications[$row['userID']] = $row['notificationID'];
		}
		
		// skip recipients with outstanding notifications
		if (!empty($notifications)) {
			// filter by author
			if ($notificationObject->getAuthorID()) {
				$conditions = new PreparedStatementConditionBuilder();
				$conditions->add("notificationID IN (?)", array(array_values($notifications)));
				$conditions->add("authorID = ?", array($notificationObject->getAuthorID()));
				
				$sql = "SELECT	notificationID
					FROM	wcf".WCF_N."_user_notification_author
					".$conditions;
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute($conditions->getParameters());
				$notificationIDs = array();
				while ($row = $statement->fetchArray()) {
					$notificationIDs[] = $row['notificationID'];
				}
				
				foreach ($notifications as $userID => $notificationID) {
					// do not skip recipients with a similar notification but authored by somebody else
					if (!in_array($notificationID, $notificationIDs)) {
						unset($notifications[$userID]);
					}
				}
			}
			
			$recipientIDs = array_diff($recipientIDs, array_keys($notifications));
			if (empty($recipientIDs)) return;
		}
		
		// get recipients
		$recipientList = new UserNotificationEventRecipientList();
		$recipientList->getConditionBuilder()->add('event_to_user.eventID = ?', array($event->eventID));
		$recipientList->getConditionBuilder()->add('event_to_user.userID IN (?)', array($recipientIDs));
		$recipientList->readObjects();
		$recipients = $recipientList->getObjects();
		if (!empty($recipients)) {
			$data = array(
				'authorID' => ($event->getAuthorID() ?: null),
				'data' => array(
					'eventID' => $event->eventID,
					'authorID' => ($event->getAuthorID() ?: null),
					'objectID' => $notificationObject->getObjectID(),
					'time' => TIME_NOW,
					'additionalData' => serialize($additionalData)
				),
				'recipients' => $recipients
			);
			
			if ($event->isStackable()) {
				$data['notifications'] = $notifications;
				
				$action = new UserNotificationAction(array(), 'createStackable', $data);
			}
			else {
				$action = new UserNotificationAction(array(), 'createDefault', $data);
			}
			
			$result = $action->executeAction();
			$notifications = $result['returnValues'];
			
			/*
			// create new notification
			$action = new UserNotificationAction(array(), 'create', array(
				'authorID' => ($event->getAuthorID() ?: null),
				'data' => array(
					'eventID' => $event->eventID,
					'authorID' => ($event->getAuthorID() ?: null),
					'objectID' => $notificationObject->getObjectID(),
					'time' => TIME_NOW,
					'additionalData' => serialize($additionalData)
				),
				'recipients' => $recipients
			));
			$result = $action->executeAction();
			$notifications = $result['returnValues'];
			*/
			
			// TODO: move -> DBOAction?
			// send notifications
			foreach ($recipients as $recipient) {
				if ($recipient->mailNotificationType == 'instant') {
					if (isset($notifications[$recipient->userID]) && $notifications[$recipient->userID]['isNew']) {
						$this->sendInstantMailNotification($notifications[$recipient->userID]['object'], $recipient, $event);
					}
				}
			}
			
			// reset notification count
			UserStorageHandler::getInstance()->reset(array_keys($recipients), 'userNotificationCount');
		}
	}
	
	/**
	 * Returns the number of outstanding notifications for the active user.
	 * 
	 * @param	boolean		$skipCache
	 * @return	integer
	 */
	public function getNotificationCount($skipCache = false) {
		if ($this->notificationCount === null || $skipCache) {
			$this->notificationCount = 0;
			
			if (WCF::getUser()->userID) {
				// load storage data
				UserStorageHandler::getInstance()->loadStorage(array(WCF::getUser()->userID));
					
				// get ids
				$data = UserStorageHandler::getInstance()->getStorage(array(WCF::getUser()->userID), 'userNotificationCount');
				
				// cache does not exist or is outdated
				if ($data[WCF::getUser()->userID] === null || $skipCache) {
					$sql = "SELECT	COUNT(*) AS count
						FROM	wcf".WCF_N."_user_notification
						WHERE	userID = ?
							AND confirmed = ?";
					$statement = WCF::getDB()->prepareStatement($sql);
					$statement->execute(array(
						WCF::getUser()->userID,
						0
					));
					
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
	 * Counts all existing notifications for current user and returns it.
	 * 
	 * @return	integer
	 */
	public function countAllNotifications() {
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_user_notification
			WHERE	userID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(WCF::getUser()->userID));
		$row = $statement->fetchArray();
		
		return $row['count'];
	}
	
	/**
	 * Returns a limited list of outstanding notifications.
	 * 
	 * @param	integer		$limit
	 * @param	integer		$offset
	 * @param	boolean		$showConfirmedNotifications
	 * @return	array<array>
	 */
	public function getNotifications($limit = 5, $offset = 0, $showConfirmedNotifications = false) {
		// build enormous query
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("notification.userID = ?", array(WCF::getUser()->userID));
		if (!$showConfirmedNotifications) $conditions->add("notification.confirmed = ?", array(0));
		
		$sql = "SELECT		notification.notificationID, notification_event.eventID, notification.authorID,
					notification.moreAuthors, object_type.objectType, notification.objectID,
					notification.additionalData,
					notification.time".($showConfirmedNotifications ? ", notification.confirmed" : "")."
			FROM		wcf".WCF_N."_user_notification notification
			LEFT JOIN	wcf".WCF_N."_user_notification_event notification_event
			ON		(notification_event.eventID = notification.eventID)
			LEFT JOIN	wcf".WCF_N."_object_type object_type
			ON		(object_type.objectTypeID = notification_event.objectTypeID)
			".$conditions."
			ORDER BY	".($showConfirmedNotifications ? "notification.confirmed ASC, " : "")."notification.time DESC";
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
		}
		
		// return an empty set if no notifications exist
		if (empty($events)) {
			return array(
				'count' => 0,
				'notifications' => array()
			);
		}
		
		// load authors
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("notificationID IN (?)", array($notificationIDs));
		$sql = "SELECT		notificationID, authorID
			FROM		wcf".WCF_N."_user_notification_author
			".$conditions."
			ORDER BY	time ASC";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		$authorIDs = $authorToNotification = array();
		while ($row = $statement->fetchArray()) {
			if (!$row['authorID']) {
				continue;
			}
			
			if (!isset($authorToNotification[$row['notificationID']])) {
				$authorToNotification[$row['notificationID']] = array();
			}
			
			$authorIDs[] = $row['authorID'];
			$authorToNotification[$row['notificationID']][] = $row['authorID'];
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
			$notificationID = $event['notificationID'];
			
			$class->setObject(
				$notificationObjects[$notificationID],
				$objectTypes[$event['objectType']]['objects'][$event['objectID']],
				(isset($authors[$event['authorID']]) ? $authors[$event['authorID']] : $unknownAuthor),
				unserialize($event['additionalData'])
			);
			
			if (isset($authorToNotification[$notificationID])) {
				$eventAuthors = array();
				foreach ($authorToNotification[$notificationID] as $userID) {
					if (isset($authors[$userID])) {
						$eventAuthors[$userID] = $authors[$userID];
					}
				}
				if (!empty($eventAuthors)) {
					$class->setAuthors($eventAuthors);
				}
			}
			
			$data = array(
				'authors' => count($class->getAuthors()),
				'event' => $class,
				'notificationID' => $event['notificationID'],
				'time' => $event['time']
			);
			
			if ($showConfirmedNotifications) {
				$data['confirmed'] = $event['confirmed'];
			}
			
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
	 * @return	\wcf\system\user\notification\event\IUserNotificationEvent
	 */
	public function getEvent($objectType, $eventName) {
		if (!isset($this->availableEvents[$objectType][$eventName])) return null;
		
		return $this->availableEvents[$objectType][$eventName];
	}
	
	/**
	 * Returns all events for given object type.
	 * 
	 * @param	string		$objectType
	 * @return	array<\wcf\system\user\notification\event\IUserNotificationEvent>
	 */
	public function getEvents($objectType) {
		if (!isset($this->availableEvents[$objectType])) return array();
		
		return $this->availableEvents[$objectType];
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
	 * @return	array<\wcf\system\user\notification\object\type\IUserNotificationObjectType>
	 */
	public function getAvailableObjectTypes() {
		return $this->availableObjectTypes;
	}
	
	/**
	 * Returns a list of available events.
	 * 
	 * @return	array<\wcf\system\user\notification\event\IUserNotificationEvent>
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
	 * @param	\wcf\data\user\notification\UserNotification			$notification
	 * @param	\wcf\data\user\User						$user
	 * @param	\wcf\system\user\notification\event\IUserNotificationEvent	$event
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
	 * This method does not delete notifications, instead it marks them as confirmed. The system
	 * does not allow to delete them, but since it was intended in WCF 2.0, this method only
	 * exists for compatibility reasons.
	 * 
	 * Please consider replacing your calls with markAsConfirmed().
	 * 
	 * @deprecated
	 * @param	string		$eventName
	 * @param	string		$objectType
	 * @param	array<integer>	$recipientIDs
	 * @param	array<integer>	$objectIDs
	 */
	public function deleteNotifications($eventName, $objectType, array $recipientIDs, array $objectIDs = array()) {
		$this->markAsConfirmed($eventName, $objectType, $recipientIDs, $objectIDs);
	}
	
	/**
	 * Marks notifications as confirmed
	 * 
	 * @param	string		$eventName
	 * @param	string		$objectType
	 * @param	array<integer>	$recipientIDs
	 * @param	array<integer>	$objectIDs
	 */
	public function markAsConfirmed($eventName, $objectType, array $recipientIDs, array $objectIDs = array()) {
		// check given object type and event name
		if (!isset($this->availableEvents[$objectType][$eventName])) {
			throw new SystemException("Unknown event ".$objectType."-".$eventName." given");
		}
		
		// get objects
		$objectTypeObject = $this->availableObjectTypes[$objectType];
		$event = $this->availableEvents[$objectType][$eventName];
		
		// mark as confirmed
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("eventID = ?", array($event->eventID));
		if (!empty($recipientIDs)) $conditions->add("userID IN (?)", array($recipientIDs));
		if (!empty($objectIDs)) $conditions->add("objectID IN (?)", array($objectIDs));
		
		$sql = "UPDATE	wcf".WCF_N."_user_notification
			SET	confirmed = ?
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$parameters = $conditions->getParameters();
		array_unshift($parameters, 1);
		$statement->execute($parameters);
		
		// delete notification_to_user assignments (mimic legacy notification system)
		$sql = "DELETE FROM	wcf".WCF_N."_user_notification_to_user
			WHERE		notificationID NOT IN (
						SELECT	notificationID
						FROM	wcf".WCF_N."_user_notification
						WHERE	confirmed = ?
					)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(0));
		
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
