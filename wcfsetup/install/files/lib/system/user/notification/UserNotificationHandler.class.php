<?php
namespace wcf\system\user\notification;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\notification\event\recipient\UserNotificationEventRecipientList;
use wcf\data\user\notification\event\UserNotificationEventList;
use wcf\data\user\notification\UserNotification;
use wcf\data\user\notification\UserNotificationAction;
use wcf\data\user\User;
use wcf\data\user\UserEditor;
use wcf\data\user\UserProfile;
use wcf\system\cache\builder\UserNotificationEventCacheBuilder;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\event\EventHandler;
use wcf\system\exception\SystemException;
use wcf\system\mail\Mail;
use wcf\system\user\notification\event\IUserNotificationEvent;
use wcf\system\user\notification\object\type\IUserNotificationObjectType;
use wcf\system\user\notification\object\IUserNotificationObject;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Handles user notifications.
 * 
 * @author	Marcel Werk, Oliver Kliebisch
 * @copyright	2001-2015 WoltLab GmbH, Oliver Kliebisch
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
	 * @var	ObjectType[]
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
	 * @param	string				$eventName
	 * @param	string				$objectType
	 * @param	IUserNotificationObject		$notificationObject
	 * @param	integer[]			$recipientIDs
	 * @param	mixed[]				$additionalData
	 * @param	integer				$baseObjectID
	 * @throws	SystemException
	 */
	public function fireEvent($eventName, $objectType, IUserNotificationObject $notificationObject, array $recipientIDs, array $additionalData = array(), $baseObjectID = 0) {
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
				$userProfile = UserProfileRuntimeCache::getInstance()->getObject($notificationObject->getAuthorID());
			}
		}
		if ($userProfile === null) {
			$userProfile = new UserProfile(new User(null, array()));
		}
		
		// set object data
		$event->setObject(new UserNotification(null, array()), $notificationObject, $userProfile, $additionalData);
		
		$parameters = array(
			'eventName' => $eventName,
			'objectType' => $objectType,
			'notificationObject' => $notificationObject,
			'recipientIDs' => $recipientIDs,
			'additionalData' => $additionalData,
			'baseObjectID' => $baseObjectID,
			'objectTypeObject' => $objectTypeObject,
			'userProfile' => $userProfile,
			'event' => $event
		);
		EventHandler::getInstance()->fireAction($this, 'fireEvent', $parameters);
		
		// find existing notifications
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("userID IN (?)", array($recipientIDs));
		$conditions->add("eventID = ?", array($event->eventID));
		$conditions->add("eventHash = ?", array($event->getEventHash()));
		$conditions->add("confirmTime = ?", array(0));
		
		$sql = "SELECT	notificationID, userID
			FROM	wcf".WCF_N."_user_notification
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		$notifications = array();
		while ($row = $statement->fetchArray()) {
			$notifications[$row['userID']] = $row['notificationID'];
		}
		
		// check if event supports stacking and author should be added
		if (!empty($notifications) && $event->isStackable()) {
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("notificationID IN (?)", array(array_values($notifications)));
			if ($notificationObject->getAuthorID()) {
				$conditions->add("authorID = ?", array($notificationObject->getAuthorID()));
			}
			else {
				$conditions->add("authorID IS NULL");
			}
			
			$sql = "SELECT	notificationID
				FROM	wcf".WCF_N."_user_notification_author
				".$conditions;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());
			$notificationIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);
			
			// filter array of existing notifications and remove values which
			// do not have a notification from this author yet (inverse logic!)
			foreach ($notifications as $userID => $notificationID) {
				if (!in_array($notificationID, $notificationIDs)) {
					unset($notifications[$userID]);
				}
			}
			
			if (!empty($notificationIDs)) {	
				// update trigger count
				$sql = "UPDATE	wcf".WCF_N."_user_notification
					SET	timesTriggered = timesTriggered + ?,
						guestTimesTriggered = guestTimesTriggered + ?
					WHERE	notificationID = ?";
				$statement = WCF::getDB()->prepareStatement($sql);
				
				WCF::getDB()->beginTransaction();
				foreach ($notificationIDs as $notificationID) {
					$statement->execute(array(
						1,
						$notificationObject->getAuthorID() ? 0 : 1,
						$notificationID
					));
				}
				WCF::getDB()->commitTransaction();
			}
		}
		
		$recipientIDs = array_diff($recipientIDs, array_keys($notifications));
		if (empty($recipientIDs)) {
			return;
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
					'baseObjectID' => $baseObjectID,
					'eventHash' => $event->getEventHash(),
					'packageID' => $objectTypeObject->packageID,
					'mailNotified' => ($event->supportsEmailNotification() ? 0 : 1),
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
				$data['data']['timesTriggered'] = 1;
				$action = new UserNotificationAction(array(), 'createDefault', $data);
			}
			
			$result = $action->executeAction();
			$notifications = $result['returnValues'];
			
			// send notifications
			if ($event->supportsEmailNotification()) {
				foreach ($recipients as $recipient) {
					if ($recipient->mailNotificationType == 'instant') {
						if (isset($notifications[$recipient->userID]) && $notifications[$recipient->userID]['isNew']) {
							$this->sendInstantMailNotification($notifications[$recipient->userID]['object'], $recipient, $event);
						}
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
				$data = UserStorageHandler::getInstance()->getField('userNotificationCount');
				
				// cache does not exist or is outdated
				if ($data === null || $skipCache) {
					$sql = "SELECT	COUNT(*)
						FROM	wcf".WCF_N."_user_notification
						WHERE	userID = ?
							AND confirmTime = ?";
					$statement = WCF::getDB()->prepareStatement($sql);
					$statement->execute(array(
						WCF::getUser()->userID,
						0
					));
					
					$this->notificationCount = $statement->fetchSingleColumn();
					
					// update storage data
					UserStorageHandler::getInstance()->update(WCF::getUser()->userID, 'userNotificationCount', serialize($this->notificationCount));
				}
				else {
					$this->notificationCount = unserialize($data);
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
		$sql = "SELECT	COUNT(*)
			FROM	wcf".WCF_N."_user_notification
			WHERE	userID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(WCF::getUser()->userID));
		
		return $statement->fetchSingleColumn();
	}
	
	/**
	 * Returns a list of notifications.
	 * 
	 * @param	integer		$limit
	 * @param	integer		$offset
	 * @param	boolean		$showConfirmedNotifications	DEPRECATED
	 * @return	mixed[]
	 */
	public function getNotifications($limit = 5, $offset = 0, $showConfirmedNotifications = false) {
		$notifications = $this->fetchNotifications($limit, $offset);
		
		return $this->processNotifications($notifications);
	}
	
	/**
	 * Returns a mixed list of notifications, containing leading unconfirmed notifications in their chronological
	 * order regardless of the overall order of already confirmed items.
	 * 
	 * @return	array
	 */
	public function getMixedNotifications() {
		$notificationCount = $this->getNotificationCount(true);
		
		$notifications = array();
		if ($notificationCount > 0) {
			$notifications = $this->fetchNotifications(5, 0, 0);
		}
		
		$count = count($notifications);
		$limit = 5 - $count;
		
		if ($limit) {
			$notifications = array_merge($notifications, $this->fetchNotifications($limit, 0, 1));
		}
		
		$returnValues = $this->processNotifications($notifications);
		$returnValues['notificationCount'] = $notificationCount;
		
		return $returnValues;
	}
	
	/**
	 * Fetches a list of notifications based upon given conditions.
	 * 
	 * @param	integer		$limit
	 * @param	integer		$offset
	 * @param	mixed		$filterByConfirmed
	 * @return	UserNotification[]
	 */
	protected function fetchNotifications($limit, $offset, $filterByConfirmed = null) {
		// build enormous query
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("notification.userID = ?", array(WCF::getUser()->userID));
		
		if ($filterByConfirmed !== null) {
			// consider only unconfirmed notifications
			if ($filterByConfirmed == 0) {
				$conditions->add("notification.confirmTime = ?", array(0));
			}
			else {
				// consider only notifications marked as confirmed in the past 48 hours (86400 = 1 day)
				$conditions->add("notification.confirmTime >= ?", array(TIME_NOW - (2 * 86400)));
			}
		}
		
		$sql = "SELECT		notification.*, notification_event.eventID, object_type.objectType
			FROM		wcf".WCF_N."_user_notification notification
			LEFT JOIN	wcf".WCF_N."_user_notification_event notification_event
			ON		(notification_event.eventID = notification.eventID)
			LEFT JOIN	wcf".WCF_N."_object_type object_type
			ON		(object_type.objectTypeID = notification_event.objectTypeID)
			".$conditions."
			ORDER BY	notification.time DESC";
		$statement = WCF::getDB()->prepareStatement($sql, $limit, $offset);
		$statement->execute($conditions->getParameters());
		
		$notifications = array();
		while ($notification = $statement->fetchObject('\wcf\data\user\notification\UserNotification')) {
			$notifications[$notification->notificationID] = $notification;
		}
		
		return $notifications;
	}
	
	/**
	 * Processes a list of notification objects.
	 * 
	 * @param	UserNotification[]	$notificationObjects
	 * @return	mixed[]
	 */
	public function processNotifications(array $notificationObjects) {
		// return an empty set if no notifications exist
		if (empty($notificationObjects)) {
			return array(
				'count' => 0,
				'notifications' => array()
			);
		}
		
		$authorIDs = $eventIDs = $notificationIDs = $objectTypes = array();
		foreach ($notificationObjects as $notification) {
			// cache object types
			if (!isset($objectTypes[$notification->objectType])) {
				$objectTypes[$notification->objectType] = array(
					'objectType' => $this->availableObjectTypes[$notification->objectType],
					'objectIDs' => array(),
					'objects' => array()
				);
			}
			
			$objectTypes[$notification->objectType]['objectIDs'][] = $notification->objectID;
			$eventIDs[] = $notification->eventID;
			$notificationIDs[] = $notification->notificationID;
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
			if ($row['authorID']) {
				$authorIDs[] = $row['authorID'];
			}
			
			if (!isset($authorToNotification[$row['notificationID']])) {
				$authorToNotification[$row['notificationID']] = array();
			}
			
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
		
		// build notification data
		$notifications = array();
		$deleteNotifications = array();
		foreach ($notificationObjects as $notification) {
			$object = $objectTypes[$notification->objectType]['objects'][$notification->objectID];
			if ($object->__unknownNotificationObject) {
				$deleteNotifications[] = $notification;
				continue;
			}
			
			$className = $eventObjects[$notification->eventID]->className;
			$class = new $className($eventObjects[$notification->eventID]);
			$class->setObject(
				$notification,
				$object,
				(isset($authors[$notification->authorID]) ? $authors[$notification->authorID] : $unknownAuthor),
				$notification->additionalData
			);
			
			if (isset($authorToNotification[$notification->notificationID])) {
				$eventAuthors = array();
				foreach ($authorToNotification[$notification->notificationID] as $userID) {
					if (!$userID) {
						$eventAuthors[0] = $unknownAuthor;
					}
					else if (isset($authors[$userID])) {
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
				'notificationID' => $notification->notificationID,
				'time' => $notification->time
			);
			
			$data['confirmed'] = ($notification->confirmTime > 0);
			
			$notifications[] = $data;
		}
		
		// check access
		foreach ($notifications as $index => $notificationData) {
			if (!$notificationData['event']->checkAccess()) {
				if ($notificationData['event']->deleteNoAccessNotification()) {
					$deleteNotifications[] = $notificationData['event']->getNotification();
				}
				
				unset($notifications[$index]);
			}
		}
		
		if (!empty($deleteNotifications)) {
			$notificationAction = new UserNotificationAction($deleteNotifications, 'delete');
			$notificationAction->executeAction();
			
			// reset notification counter
			UserStorageHandler::getInstance()->reset(array(WCF::getUser()->userID), 'userNotificationCount');
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
	 * @return	IUserNotificationEvent[]
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
	 * @throws	SystemException
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
	 * @return	IUserNotificationObjectType[]
	 */
	public function getAvailableObjectTypes() {
		return $this->availableObjectTypes;
	}
	
	/**
	 * Returns a list of available events.
	 * 
	 * @return	IUserNotificationEvent[]
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
		// no notifications for disabled or banned users
		if ($user->activationCode) return;
		if ($user->banned) return;
		
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
	 * 
	 * @param	string		$eventName
	 * @param	string		$objectType
	 * @param	integer[]	$recipientIDs
	 * @param	integer[]	$objectIDs
	 */
	public function deleteNotifications($eventName, $objectType, array $recipientIDs, array $objectIDs = array()) {
		$this->markAsConfirmed($eventName, $objectType, $recipientIDs, $objectIDs);
	}
	
	/**
	 * Removes notifications, this method should only be invoked for delete objects.
	 * 
	 * @param	string		$objectType
	 * @param	integer[]	$objectIDs
	 * @throws	SystemException
	 */
	public function removeNotifications($objectType, array $objectIDs) {
		// check given object type
		$objectTypeObj = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.notification.objectType', $objectType);
		if ($objectTypeObj === null) {
			throw new SystemException("Unknown object type ".$objectType." given");
		}
		
		// get event ids
		$sql = "SELECT	eventID
			FROM	wcf".WCF_N."_user_notification_event
			WHERE	objectTypeID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$objectTypeObj->objectTypeID
		));
		$eventIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);
		
		if (!empty($eventIDs)) {
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("eventID IN (?)", array($eventIDs));
			$conditions->add("objectID IN (?)", array($objectIDs));
			
			$sql = "SELECT	userID
				FROM	wcf".WCF_N."_user_notification
				".$conditions;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());
			$userIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);
			
			// reset number of notifications
			if (!empty($userIDs)) {
				UserStorageHandler::getInstance()->reset(array_unique($userIDs), 'userNotificationCount');
			}
			
			// delete notifications
			$sql = "DELETE FROM	wcf".WCF_N."_user_notification
				".$conditions;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());
		}
	}
	
	/**
	 * Marks notifications as confirmed
	 * 
	 * @param	string		$eventName
	 * @param	string		$objectType
	 * @param	integer[]	$recipientIDs
	 * @param	integer[]	$objectIDs
	 * @throws	SystemException
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
			SET	confirmTime = ?
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$parameters = $conditions->getParameters();
		array_unshift($parameters, TIME_NOW);
		$statement->execute($parameters);
		
		// delete notification_to_user assignments (mimic legacy notification system)
		$sql = "DELETE FROM	wcf".WCF_N."_user_notification_to_user
			WHERE		notificationID NOT IN (
						SELECT	notificationID
						FROM	wcf".WCF_N."_user_notification
						WHERE	confirmTime = ?
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
	 * Marks a single notification id as confirmed.
	 * 
	 * @param	integer		$notificationID
	 */
	public function markAsConfirmedByID($notificationID) {
		$this->markAsConfirmedByIDs(array($notificationID));
	}
	
	/**
	 * Marks a list of notification ids as confirmed.
	 * 
	 * @param	integer[]	$notificationIDs
	 */
	public function markAsConfirmedByIDs(array $notificationIDs) {
		if (empty($notificationIDs)) {
			return;
		}
		
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("notificationID IN (?)", array($notificationIDs));
		
		// mark notifications as confirmed
		$sql = "UPDATE	wcf".WCF_N."_user_notification
			SET	confirmTime = ?
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$parameters = $conditions->getParameters();
		array_unshift($parameters, TIME_NOW);
		$statement->execute($parameters);
		
		// delete notification_to_user assignments (mimic legacy notification system)
		$sql = "DELETE FROM	wcf".WCF_N."_user_notification_to_user
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		
		// reset user storage
		UserStorageHandler::getInstance()->reset(array(WCF::getUser()->userID), 'userNotificationCount');
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
