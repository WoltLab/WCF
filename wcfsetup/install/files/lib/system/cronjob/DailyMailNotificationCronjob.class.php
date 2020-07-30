<?php
namespace wcf\system\cronjob;
use wcf\data\cronjob\Cronjob;
use wcf\data\user\notification\event\UserNotificationEventList;
use wcf\data\user\notification\UserNotification;
use wcf\data\user\User;
use wcf\data\user\UserEditor;
use wcf\data\user\UserList;
use wcf\data\user\UserProfile;
use wcf\form\NotificationUnsubscribeForm;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\email\mime\MimePartFacade;
use wcf\system\email\mime\RecipientAwareTextMimePart;
use wcf\system\email\Email;
use wcf\system\email\UserMailbox;
use wcf\system\request\LinkHandler;
use wcf\system\user\notification\event\IUserNotificationEvent;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\WCF;

/**
 * Sends daily mail notifications.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cronjob
 */
class DailyMailNotificationCronjob extends AbstractCronjob {
	/**
	 * @inheritDoc
	 */
	public function execute(Cronjob $cronjob) {
		parent::execute($cronjob);
		
		// get user ids
		$sql = "SELECT	DISTINCT userID
			FROM	wcf".WCF_N."_user_notification
			WHERE	mailNotified = ?
				AND time < ?
				AND confirmTime = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			0,
			TIME_NOW - 3600 * 23,
			0
		]);
		$userIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);
		if (empty($userIDs)) return;
		
		// get users
		$userList = new UserList();
		$userList->setObjectIDs($userIDs);
		$userList->readObjects();
		$users = $userList->getObjects();
		
		// get notifications
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("notification.userID IN (?)", [$userIDs]);
		$conditions->add("notification.mailNotified = ?", [0]);
		$conditions->add("notification.confirmTime = ?", [0]);
		
		$sql = "SELECT		notification.*, notification_event.eventID, object_type.objectType
			FROM		wcf".WCF_N."_user_notification notification
			LEFT JOIN	wcf".WCF_N."_user_notification_event notification_event
			ON		(notification_event.eventID = notification.eventID)
			LEFT JOIN	wcf".WCF_N."_object_type object_type
			ON		(object_type.objectTypeID = notification_event.objectTypeID)
			".$conditions."
			ORDER BY	notification.time";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		
		// mark notifications as done
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("userID IN (?)", [$userIDs]);
		$conditions->add("mailNotified = ?", [0]);
		$sql = "UPDATE	wcf".WCF_N."_user_notification
			SET	mailNotified = 1
			".$conditions;
		$statement2 = WCF::getDB()->prepareStatement($sql);
		$statement2->execute($conditions->getParameters());
		
		// collect data
		$eventsToUser = $objectTypes = $eventIDs = $notificationObjects = [];
		$availableObjectTypes = UserNotificationHandler::getInstance()->getAvailableObjectTypes();
		while ($row = $statement->fetchArray()) {
			if (!isset($eventsToUser[$row['userID']])) $eventsToUser[$row['userID']] = [];
			$eventsToUser[$row['userID']][] = $row['notificationID'];
			
			// cache object types
			if (!isset($objectTypes[$row['objectType']])) {
				$objectTypes[$row['objectType']] = [
					'objectType' => $availableObjectTypes[$row['objectType']],
					'objectIDs' => [],
					'objects' => []
				];
			}
			
			$objectTypes[$row['objectType']]['objectIDs'][] = $row['objectID'];
			$eventIDs[] = $row['eventID'];
			
			$notificationObjects[$row['notificationID']] = new UserNotification(null, $row);
		}
		
		// load authors
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("notificationID IN (?)", [array_keys($notificationObjects)]);
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
		$authors = UserProfileRuntimeCache::getInstance()->getObjects($authorIDs);
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
		
		foreach ($eventsToUser as $userID => $notificationIDs) {
			if (!isset($users[$userID])) continue;
			$user = $users[$userID];
			
			// no notifications for disabled or banned users
			if (!$user->isEmailConfirmed()) continue;
			if ($user->banned) continue;
			
			$notifications = array_map(function ($notificationID) use ($notificationObjects, $eventObjects, $user, $objectTypes, $authors, $authorToNotification, $unknownAuthor) {
				$notification = $notificationObjects[$notificationID];
				
				$className = $eventObjects[$notification->eventID]->className;
				
				/** @var IUserNotificationEvent $class */
				$class = new $className($eventObjects[$notification->eventID]);
				$class->setObject(
					$notification,
					$objectTypes[$notification->objectType]['objects'][$notification->objectID],
					(isset($authors[$notification->authorID]) ? $authors[$notification->authorID] : $unknownAuthor),
					$notification->additionalData
				);
				$class->setLanguage($user->getLanguage());
				
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
				
				$message = $class->getEmailMessage('daily');
				if (is_array($message)) {
					if (!isset($message['variables'])) $message['variables'] = [];
					$variables = array_merge($message['variables'], [
						'notificationContent' => $message,
						'event' => $class,
						'notificationType' => 'daily',
						'variables' => $message['variables'] // deprecated, but is kept for backwards compatibility
					]);
					
					return $variables;
				}
				else {
					return [
						'notificationContent' => $message,
						'event' => $class,
						'notificationType' => 'daily'
					];
				}
			}, $notificationIDs);
			
			// generate token if not present
			if (!$user->notificationMailToken) {
				$token = bin2hex(\random_bytes(10));
				$editor = new UserEditor($user);
				$editor->update(['notificationMailToken' => $token]);
				
				// reload user
				$user = new User($user->userID);
			}
			
			$email = new Email();
			$email->setSubject($user->getLanguage()->getDynamicVariable('wcf.user.notification.mail.daily.subject', ['count' => count($notifications)]));
			$email->addRecipient(new UserMailbox($user));
			$email->setListID('daily.notification');
			$email->setListUnsubscribe(LinkHandler::getInstance()->getControllerLink(NotificationUnsubscribeForm::class, [
				'userID' => $user->userID,
				'token' => $user->notificationMailToken,
			]), true);
			
			$html = new RecipientAwareTextMimePart('text/html', 'email_dailyNotification', 'wcf', ['notifications' => $notifications]);
			$plainText = new RecipientAwareTextMimePart('text/plain', 'email_dailyNotification', 'wcf', ['notifications' => $notifications]);
			$email->setBody(new MimePartFacade([$html, $plainText]));
			
			$email->send();
		}
	}
}
