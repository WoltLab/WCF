<?php
namespace wcf\system\cronjob;
use wcf\data\cronjob\Cronjob;
use wcf\data\user\notification\event\UserNotificationEventList;
use wcf\data\user\notification\UserNotification;
use wcf\data\user\User;
use wcf\data\user\UserEditor;
use wcf\data\user\UserList;
use wcf\data\user\UserProfile;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\mail\Mail;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Sends daily mail notifications.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cronjob
 * @category	Community Framework
 */
class DailyMailNotificationCronjob extends AbstractCronjob {
	/**
	 * @see	\wcf\system\cronjob\ICronjob::execute()
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
		$statement->execute(array(
			0,
			TIME_NOW - 3600 * 23,
			0
		));
		$userIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);
		if (empty($userIDs)) return;
		
		// get users
		$userList = new UserList();
		$userList->setObjectIDs($userIDs);
		$userList->readObjects();
		$users = $userList->getObjects();
		
		// get notifications
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("notification.userID IN (?)", array($userIDs));
		$conditions->add("notification.mailNotified = ?", array(0));
		$conditions->add("notification.confirmTime = ?", array(0));
		
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
		$conditions->add("userID IN (?)", array($userIDs));
		$conditions->add("mailNotified = ?", array(0));
		$sql = "UPDATE	wcf".WCF_N."_user_notification
			SET	mailNotified = 1
			".$conditions;
		$statement2 = WCF::getDB()->prepareStatement($sql);
		$statement2->execute($conditions->getParameters());
		
		// collect data
		$eventsToUser = $objectTypes = $eventIDs = $notificationObjects = array();
		$availableObjectTypes = UserNotificationHandler::getInstance()->getAvailableObjectTypes();
		while ($row = $statement->fetchArray()) {
			if (!isset($eventsToUser[$row['userID']])) $eventsToUser[$row['userID']] = array();
			$eventsToUser[$row['userID']][] = $row['notificationID'];
			
			// cache object types
			if (!isset($objectTypes[$row['objectType']])) {
				$objectTypes[$row['objectType']] = array(
					'objectType' => $availableObjectTypes[$row['objectType']],
					'objectIDs' => array(),
					'objects' => array()
				);
			}
			
			$objectTypes[$row['objectType']]['objectIDs'][] = $row['objectID'];
			$eventIDs[] = $row['eventID'];
			
			$notificationObjects[$row['notificationID']] = new UserNotification(null, $row);
		}
		
		// load authors
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("notificationID IN (?)", array(array_keys($notificationObjects)));
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
		$authors = UserProfileRuntimeCache::getInstance()->getObjects($authorIDs);
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
		
		foreach ($eventsToUser as $userID => $events) {
			if (!isset($users[$userID])) continue;
			$user = $users[$userID];
			
			// no notifications for disabled or banned users
			if ($user->activationCode) continue;
			if ($user->banned) continue;
			
			// add mail header
			$message = $user->getLanguage()->getDynamicVariable('wcf.user.notification.mail.header', array(
				'user' => $user
			));
			
			foreach ($events as $notificationID) {
				$notification = $notificationObjects[$notificationID];
				
				$className = $eventObjects[$notification->eventID]->className;
				$class = new $className($eventObjects[$notification->eventID]);
				$class->setObject(
					$notification,
					$objectTypes[$notification->objectType]['objects'][$notification->objectID],
					(isset($authors[$notification->authorID]) ? $authors[$notification->authorID] : $unknownAuthor),
					$notification->additionalData
				);
				$class->setLanguage($user->getLanguage());
				
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
				
				$message .= "\n\n";
				$message .= $class->getEmailMessage('daily');
			}
			
			// append notification mail footer
			$token = $user->notificationMailToken;
			if (!$token) {
				// generate token if not present
				$token = mb_substr(StringUtil::getHash(serialize(array($user->userID, StringUtil::getRandomID()))), 0, 20);
				$editor = new UserEditor($user);
				$editor->update(array('notificationMailToken' => $token));
			}
			
			$message .= "\n\n";
			$message .= $user->getLanguage()->getDynamicVariable('wcf.user.notification.mail.daily.footer', array(
				'user' => $user,
				'token' => $token
			));
			
			// build mail
			$mail = new Mail(array($user->username => $user->email), $user->getLanguage()->getDynamicVariable('wcf.user.notification.mail.daily.subject', array('count' => count($events))), $message);
			$mail->setLanguage($user->getLanguage());
			$mail->send();
		}
	}
}
