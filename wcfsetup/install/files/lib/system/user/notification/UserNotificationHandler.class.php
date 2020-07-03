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
use wcf\form\NotificationUnsubscribeForm;
use wcf\system\background\job\NotificationEmailDeliveryBackgroundJob;
use wcf\system\background\BackgroundQueueHandler;
use wcf\system\cache\builder\UserNotificationEventCacheBuilder;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\email\mime\MimePartFacade;
use wcf\system\email\mime\RecipientAwareTextMimePart;
use wcf\system\email\Email;
use wcf\system\email\UserMailbox;
use wcf\system\event\EventHandler;
use wcf\system\exception\SystemException;
use wcf\system\request\LinkHandler;
use wcf\system\user\notification\event\IUserNotificationEvent;
use wcf\system\user\notification\object\type\IUserNotificationObjectType;
use wcf\system\user\notification\object\IUserNotificationObject;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Handles user notifications.
 * 
 * @author	Marcel Werk, Oliver Kliebisch
 * @copyright	2001-2019 WoltLab GmbH, Oliver Kliebisch
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Notification
 */
class UserNotificationHandler extends SingletonFactory {
	/**
	 * list of available object types
	 * @var	IUserNotificationObjectType[]
	 */
	protected $availableObjectTypes = [];
	
	/**
	 * list of available events
	 * @var	IUserNotificationEvent[][]
	 */
	protected $availableEvents = [];
	
	/**
	 * number of outstanding notifications
	 * @var	integer
	 */
	protected $notificationCount = null;
	
	/**
	 * list of object types
	 * @var	ObjectType[]
	 */
	protected $objectTypes = [];
	
	/**
	 * @inheritDoc
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
	public function fireEvent($eventName, $objectType, IUserNotificationObject $notificationObject, array $recipientIDs, array $additionalData = [], $baseObjectID = 0) {
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
			$userProfile = new UserProfile(new User(null, []));
		}
		
		// set object data
		$event->setObject(new UserNotification(null, []), $notificationObject, $userProfile, $additionalData);
		$event->setAuthors([$event->getAuthorID() => $event->getAuthor()]);
		
		$parameters = [
			'eventName' => $eventName,
			'objectType' => $objectType,
			'notificationObject' => $notificationObject,
			'recipientIDs' => $recipientIDs,
			'additionalData' => $additionalData,
			'baseObjectID' => $baseObjectID,
			'objectTypeObject' => $objectTypeObject,
			'userProfile' => $userProfile,
			'event' => $event
		];
		// @deprecated 5.2 This event exposes incomplete data and should not be used, please use the following events instead.
		EventHandler::getInstance()->fireAction($this, 'fireEvent', $parameters);
		
		// find existing notifications
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("userID IN (?)", [$recipientIDs]);
		$conditions->add("eventID = ?", [$event->eventID]);
		$conditions->add("eventHash = ?", [$event->getEventHash()]);
		$conditions->add("confirmTime = ?", [0]);
		
		$sql = "SELECT	notificationID, userID
			FROM	wcf".WCF_N."_user_notification
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		$notifications = $statement->fetchMap('userID', 'notificationID');
		
		// check if event supports stacking and author should be added
		if (!empty($notifications) && $event->isStackable()) {
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("notificationID IN (?)", [array_values($notifications)]);
			if ($notificationObject->getAuthorID()) {
				$conditions->add("authorID = ?", [$notificationObject->getAuthorID()]);
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
					$statement->execute([
						1,
						$notificationObject->getAuthorID() ? 0 : 1,
						$notificationID
					]);
				}
				WCF::getDB()->commitTransaction();
				
				$triggerCountParameters = $parameters;
				$triggerCountParameters['updateTriggerCount'] = $notificationIDs;
				EventHandler::getInstance()->fireAction($this, 'updateTriggerCount', $triggerCountParameters);
				unset($triggerCountParameters);
			}
		}
		
		$recipientIDs = array_diff($recipientIDs, array_keys($notifications));
		if (empty($recipientIDs)) {
			return;
		}
		
		// remove recipients that are blocking the current user
		if ($userProfile->getUserID()) {
			// we use a live query here to avoid offloading this to the UserProfile
			// class, as we're potentially dealing with a lot of users and loading
			// their user storage data can get really expensive
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("userID IN (?)", [$recipientIDs]);
			$conditions->add("ignoreUserID = ?", [$userProfile->getUserID()]);
			
			$sql = "SELECT  userID
				FROM    wcf" . WCF_N . "_user_ignore
				".$conditions;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());
			$userIDs = [];
			while ($userID = $statement->fetchColumn()) {
				$userIDs[] = $userID;
			}
			
			if (!empty($userIDs)) {
				$recipientIDs = array_diff($recipientIDs, $userIDs);
			}
			
			if (empty($recipientIDs)) {
				return;
			}
		}
		
		// get recipients
		$recipientList = new UserNotificationEventRecipientList();
		$recipientList->getConditionBuilder()->add('event_to_user.eventID = ?', [$event->eventID]);
		$recipientList->getConditionBuilder()->add('event_to_user.userID IN (?)', [$recipientIDs]);
		$recipientList->readObjects();
		$recipients = $recipientList->getObjects();
		if (!empty($recipients)) {
			$data = [
				'authorID' => $event->getAuthorID() ?: null,
				'data' => [
					'eventID' => $event->eventID,
					'authorID' => $event->getAuthorID() ?: null,
					'objectID' => $notificationObject->getObjectID(),
					'baseObjectID' => $baseObjectID,
					'eventHash' => $event->getEventHash(),
					'packageID' => $objectTypeObject->packageID,
					'mailNotified' => $event->supportsEmailNotification() ? 0 : 1,
					'time' => TIME_NOW,
					'additionalData' => serialize($additionalData)
				],
				'recipients' => $recipients
			];
			
			if ($event->isStackable()) {
				$data['notifications'] = $notifications;
				
				$action = new UserNotificationAction([], 'createStackable', $data);
			}
			else {
				$data['data']['timesTriggered'] = 1;
				$action = new UserNotificationAction([], 'createDefault', $data);
			}
			
			$result = $action->executeAction();
			$notifications = $result['returnValues'];
			
			// send notifications
			if ($event->supportsEmailNotification()) {
				foreach ($recipients as $recipient) {
					if ($recipient->mailNotificationType == 'instant') {
						if (isset($notifications[$recipient->userID]) && $notifications[$recipient->userID]['isNew']) {
							$event->setObject($notifications[$recipient->userID]['object'], $notificationObject, $userProfile, $additionalData);
							$event->setAuthors([$userProfile->userID => $userProfile]);
							$this->sendInstantMailNotification($notifications[$recipient->userID]['object'], $recipient, $event);
						}
					}
				}
			}
			
			// reset notification count
			UserStorageHandler::getInstance()->reset(array_keys($recipients), 'userNotificationCount');
			
			$parameters['notifications'] = $notifications;
			$parameters['recipients'] = $recipients;
			EventHandler::getInstance()->fireAction($this, 'createdNotification', $parameters);
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
					$statement->execute([
						WCF::getUser()->userID,
						0
					]);
					
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
		$statement->execute([WCF::getUser()->userID]);
		
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
		
		$notifications = [];
		if ($notificationCount > 0) {
			$notifications = $this->fetchNotifications(10, 0, 0);
		}
		
		$count = count($notifications);
		$limit = 10 - $count;
		
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
		$conditions->add("notification.userID = ?", [WCF::getUser()->userID]);
		
		if ($filterByConfirmed !== null) {
			// consider only unconfirmed notifications
			if ($filterByConfirmed == 0) {
				$conditions->add("notification.confirmTime = ?", [0]);
			}
			else {
				// consider only notifications marked as confirmed in the past 48 hours (86400 = 1 day)
				$conditions->add("notification.confirmTime >= ?", [TIME_NOW - (2 * 86400)]);
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
		
		return $statement->fetchObjects(UserNotification::class, 'notificationID');
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
			return [
				'count' => 0,
				'notifications' => []
			];
		}
		
		$eventIDs = $notificationIDs = $objectTypes = [];
		foreach ($notificationObjects as $notification) {
			// cache object types
			if (!isset($objectTypes[$notification->objectType])) {
				$objectTypes[$notification->objectType] = [
					'objectType' => $this->availableObjectTypes[$notification->objectType],
					'objectIDs' => [],
					'objects' => []
				];
			}
			
			$objectTypes[$notification->objectType]['objectIDs'][] = $notification->objectID;
			$eventIDs[] = $notification->eventID;
			$notificationIDs[] = $notification->notificationID;
		}
		
		// load authors
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("notificationID IN (?)", [$notificationIDs]);
		$sql = "SELECT		notificationID, authorID
			FROM		wcf".WCF_N."_user_notification_author
			".$conditions."
			ORDER BY	time ASC";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		$authorIDs = $authorToNotification = [];
		while ($row = $statement->fetchArray()) {
			if ($row['authorID']) {
				$authorIDs[] = $row['authorID'];
			}
			
			if (!isset($authorToNotification[$row['notificationID']])) {
				$authorToNotification[$row['notificationID']] = [];
			}
			
			$authorToNotification[$row['notificationID']][] = $row['authorID'];
		}
		
		// load authors
		$authors = UserProfile::getUserProfiles($authorIDs);
		$unknownAuthor = new UserProfile(new User(null, ['userID' => null, 'username' => WCF::getLanguage()->get('wcf.user.guest')]));
		
		// load objects associated with each object type
		foreach ($objectTypes as $objectType => $objectData) {
			/** @noinspection PhpUndefinedMethodInspection */
			$objectTypes[$objectType]['objects'] = $objectData['objectType']->getObjectsByIDs($objectData['objectIDs']);
		}
		
		// load required events
		$eventList = new UserNotificationEventList();
		$eventList->getConditionBuilder()->add("user_notification_event.eventID IN (?)", [$eventIDs]);
		$eventList->readObjects();
		$eventObjects = $eventList->getObjects();
		
		// build notification data
		$notifications = [];
		$deleteNotifications = [];
		foreach ($notificationObjects as $notification) {
			$object = $objectTypes[$notification->objectType]['objects'][$notification->objectID];
			if ($object->__unknownNotificationObject) {
				$deleteNotifications[] = $notification;
				continue;
			}
			
			$className = $eventObjects[$notification->eventID]->className;
			
			/** @var IUserNotificationEvent $class */
			$class = new $className($eventObjects[$notification->eventID]);
			$class->setObject(
				$notification,
				$object,
				(isset($authors[$notification->authorID]) ? $authors[$notification->authorID] : $unknownAuthor),
				$notification->additionalData
			);
			
			if (isset($authorToNotification[$notification->notificationID])) {
				$eventAuthors = [];
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
			
			$data = [
				'authors' => count($class->getAuthors()),
				'event' => $class,
				'notificationID' => $notification->notificationID,
				'time' => $notification->time
			];
			
			$data['confirmed'] = ($notification->confirmTime > 0);
			
			$notifications[] = $data;
		}
		
		// check access
		foreach ($notifications as $index => $notificationData) {
			/** @var IUserNotificationEvent $event */
			$event = $notificationData['event'];
			if (!$event->checkAccess()) {
				if ($event->deleteNoAccessNotification()) {
					$deleteNotifications[] = $event->getNotification();
				}
				
				unset($notifications[$index]);
			}
		}
		
		if (!empty($deleteNotifications)) {
			$notificationAction = new UserNotificationAction($deleteNotifications, 'delete');
			$notificationAction->executeAction();
			
			// reset notification counter
			UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'userNotificationCount');
		}
		
		return [
			'count' => count($notifications),
			'notifications' => $notifications
		];
	}
	
	/**
	 * Returns event object for given object type and event, returns NULL on failure.
	 * 
	 * @param	string		$objectType
	 * @param	string		$eventName
	 * @return	IUserNotificationEvent
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
		if (!isset($this->availableEvents[$objectType])) return [];
		
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
		$conditions->add("eventID = ?", [$eventID]);
		$conditions->add("objectID = ?", [$objectID]);
		if ($authorID !== null) $conditions->add("authorID = ?", [$authorID]);
		if ($time !== null) $conditions->add("time = ?", [$time]);
		
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
	 * @return	IUserNotificationEvent[][]
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
	 * Returns the processor of the object type with the given name or `null`
	 * if no such processor exists
	 * 
	 * @param	string		$objectType
	 * @return	IUserNotificationObjectType|null
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
	 * @param	UserNotification		$notification
	 * @param	User				$user
	 * @param	IUserNotificationEvent		$event
	 */
	public function sendInstantMailNotification(UserNotification $notification, User $user, IUserNotificationEvent $event) {
		// no notifications for disabled or banned users
		if (!$user->isEmailConfirmed()) return;
		if ($user->banned) return;
		
		// recipient's language
		$event->setLanguage($user->getLanguage());
		
		// generate token if not present
		if (!$user->notificationMailToken) {
			$token = bin2hex(\random_bytes(10));
			$editor = new UserEditor($user);
			$editor->update(['notificationMailToken' => $token]);
			
			// reload user
			$user = new User($user->userID);
		}
		
		$email = new Email();
		$email->setSubject($user->getLanguage()->getDynamicVariable('wcf.user.notification.mail.subject', [
			'title' => $event->getEmailTitle()
		]));
		$email->addRecipient(new UserMailbox($user));
		$humanReadableListId = $user->getLanguage()->getDynamicVariable('wcf.user.notification.'.$event->objectType.'.'.$event->eventName);
		$email->setListID($event->eventName.'.'.$event->objectType.'.instant.notification', $humanReadableListId);
		$email->setListUnsubscribe(LinkHandler::getInstance()->getControllerLink(NotificationUnsubscribeForm::class, [
			// eventID is not part of the parameter list, because we can't communicate that
			// only a single type would be unsubscribed.
			// The recipient's expectations when performing the One-Click unsubscribing are that
			// no further emails will be received. Not following that expectation might result in
			// harsh filtering.
			'userID' => $user->userID,
			'token' => $user->notificationMailToken,
		]), true);
		
		$message = $event->getEmailMessage('instant');
		if (is_array($message)) {
			if (!isset($message['variables'])) $message['variables'] = [];
			$variables = array_merge($message['variables'], [
				'notificationContent' => $message,
				'event' => $event,
				'notificationType' => 'instant',
				'variables' => $message['variables'] // deprecated, but is kept for backwards compatibility
			]);
			
			if (isset($message['message-id'])) {
				$email->setMessageID($message['message-id']);
			}
			if (isset($message['in-reply-to'])) {
				foreach ($message['in-reply-to'] as $inReplyTo) $email->addInReplyTo($inReplyTo);
			}
			if (isset($message['references'])) {
				foreach ($message['references'] as $references) $email->addReferences($references);
			}
			
			$html = new RecipientAwareTextMimePart('text/html', 'email_notification', 'wcf', $variables);
			$plainText = new RecipientAwareTextMimePart('text/plain', 'email_notification', 'wcf', $variables);
			$email->setBody(new MimePartFacade([$html, $plainText]));
		}
		else {
			$email->setBody(new RecipientAwareTextMimePart('text/plain', 'email_notification', 'wcf', [
				'notificationContent' => $message,
				'event' => $event,
				'notificationType' => 'instant'
			]));
		}
		
		$jobs = $email->getJobs();
		foreach ($jobs as $job) {
			$wrappedJob = new NotificationEmailDeliveryBackgroundJob($job, $notification, $user);
			BackgroundQueueHandler::getInstance()->enqueueIn($wrappedJob);
		}
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
	public function deleteNotifications($eventName, $objectType, array $recipientIDs, array $objectIDs = []) {
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
		$statement->execute([
			$objectTypeObj->objectTypeID
		]);
		$eventIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);
		
		if (!empty($eventIDs)) {
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("eventID IN (?)", [$eventIDs]);
			$conditions->add("objectID IN (?)", [$objectIDs]);
			
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
			
			$parameters = [
				'eventIDs' => $eventIDs,
				'objectIDs' => $objectIDs,
				'objectType' => $objectType,
				'userIDs' => $userIDs,
			];
			EventHandler::getInstance()->fireAction($this, 'removeNotifications', $parameters);
			
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
	public function markAsConfirmed($eventName, $objectType, array $recipientIDs, array $objectIDs = []) {
		// check given object type and event name
		if (!isset($this->availableEvents[$objectType][$eventName])) {
			throw new SystemException("Unknown event ".$objectType."-".$eventName." given");
		}
		
		// get objects
		$event = $this->availableEvents[$objectType][$eventName];
		
		// mark as confirmed
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("eventID = ?", [$event->eventID]);
		if (!empty($recipientIDs)) $conditions->add("userID IN (?)", [$recipientIDs]);
		if (!empty($objectIDs)) $conditions->add("objectID IN (?)", [$objectIDs]);
		
		$sql = "UPDATE	wcf".WCF_N."_user_notification
			SET	confirmTime = ?
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$parameters = $conditions->getParameters();
		array_unshift($parameters, TIME_NOW);
		$statement->execute($parameters);
		
		$parameters = [
			'event' => $event,
			'eventName' => $eventName,
			'objectIDs' => $objectIDs,
			'objectType' => $objectType,
			'recipientIDs' => $recipientIDs,
		];
		EventHandler::getInstance()->fireAction($this, 'markAsConfirmed', $parameters);
		
		// delete notification_to_user assignments (mimic legacy notification system)
		$sql = "DELETE FROM	wcf".WCF_N."_user_notification_to_user
			WHERE		notificationID NOT IN (
						SELECT	notificationID
						FROM	wcf".WCF_N."_user_notification
						WHERE	confirmTime = ?
					)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([0]);
		
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
	 * @deprecated 5.2 Please use `UserNotificationHandler::markAsConfirmedByIDs()` instead.
	 */
	public function markAsConfirmedByID($notificationID) {
		$this->markAsConfirmedByIDs([$notificationID]);
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
		$conditions->add("notificationID IN (?)", [$notificationIDs]);
		
		// mark notifications as confirmed
		$sql = "UPDATE	wcf".WCF_N."_user_notification
			SET	confirmTime = ?
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$parameters = $conditions->getParameters();
		array_unshift($parameters, TIME_NOW);
		$statement->execute($parameters);
		
		$parameters = ['notificationIDs' => $notificationIDs];
		EventHandler::getInstance()->fireAction($this, 'markAsConfirmedByIDs', $parameters);
		
		// delete notification_to_user assignments (mimic legacy notification system)
		$sql = "DELETE FROM	wcf".WCF_N."_user_notification_to_user
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		
		// reset user storage
		UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'userNotificationCount');
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
		$statement->execute([$event->eventID, WCF::getUser()->userID]);
		$row = $statement->fetchArray();
		if ($row === false) return false;
		return $row['mailNotificationType'];
	}
	
	/**
	 * Returns the title and text-only message body for the latest notification,
	 * that is both unread and newer than `$lastRequestTimestamp`. May return an
	 * empty array if there is no new notification.
	 * 
	 * @param       integer         $lastRequestTimestamp
	 * @return      string[]
	 */
	public function getLatestNotification($lastRequestTimestamp) {
		$notifications = $this->fetchNotifications(1, 0, 0);
		if (!empty($notifications) && reset($notifications)->time > $lastRequestTimestamp) {
			$notifications = $this->processNotifications($notifications);
			
			if (isset($notifications['notifications'][0])) {
				/** @var IUserNotificationEvent $event */
				$event = $notifications['notifications'][0]['event'];
				
				return [
					'title' => strip_tags($event->getTitle()),
					'message' => strip_tags($event->getMessage()),
					'link' => LinkHandler::getInstance()->getLink('NotificationConfirm', ['id' => $event->getNotification()->notificationID])
				];
			}
		}
		
		return [];
	}
}
