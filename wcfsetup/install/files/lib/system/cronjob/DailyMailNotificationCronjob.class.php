<?php
namespace wcf\system\cronjob;
use wcf\data\cronjob\Cronjob;
use wcf\data\user\notification\event\UserNotificationEventList;
use wcf\data\user\notification\UserNotificationList;
use wcf\data\user\UserEditor;
use wcf\data\user\UserList;
use wcf\data\user\UserProfile;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\mail\Mail;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Sends daily mail notifications.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2014 WoltLab GmbH
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
		$userIDs = array();
		$sql = "SELECT	DISTINCT notification_to_user.userID
			FROM	wcf".WCF_N."_user_notification_to_user notification_to_user,
				wcf".WCF_N."_user_notification notification
			WHERE	notification.notificationID = notification_to_user.notificationID
				AND notification_to_user.mailNotified = 0
				AND notification.time < ".(TIME_NOW - 3600 * 23);
		$statement = WCF::getDB()->prepareStatement($sql, 250);
		$statement->execute();
		while ($row = $statement->fetchArray()) {
			$userIDs[] = $row['userID'];
		}
		if (empty($userIDs)) return;
		
		// get users
		$userList = new UserList();
		$userList->setObjectIDs($userIDs);
		$userList->readObjects();
		$users = $userList->getObjects();
		
		// get notifications
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("notification.notificationID = notification_to_user.notificationID");
		$conditions->add("notification_to_user.userID IN (?)", array($userIDs));
		$conditions->add("notification_to_user.mailNotified = ?", array(0));
		
		$sql = "SELECT		notification_to_user.notificationID, notification_event.eventID,
					object_type.objectType, notification.objectID,
					notification.additionalData, notification.authorID,
					notification.time, notification_to_user.userID
			FROM		wcf".WCF_N."_user_notification_to_user notification_to_user,
					wcf".WCF_N."_user_notification notification
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
		$sql = "UPDATE	wcf".WCF_N."_user_notification_to_user
			SET	mailNotified = 1
			".$conditions;
		$statement2 = WCF::getDB()->prepareStatement($sql);
		$statement2->execute($conditions->getParameters());
		
		// collect data
		$authorIDs = $eventsToUser = $objectTypes = $eventIDs = $notificationIDs = array();
		$availableObjectTypes = UserNotificationHandler::getInstance()->getAvailableObjectTypes();
		while ($row = $statement->fetchArray()) {
			if (!isset($eventsToUser[$row['userID']])) $eventsToUser[$row['userID']] = array();
			$eventsToUser[$row['userID']][] = $row;
			
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
			$notificationIDs[] = $row['notificationID'];
			$authorIDs[] = $row['authorID'];
		}
		
		// load authors
		$authors = UserProfile::getUserProfiles($authorIDs);
		
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
		
		foreach ($eventsToUser as $userID => $events) {
			if (!isset($users[$userID])) continue;
			$user = $users[$userID];
			
			// add mail header
			$message = $user->getLanguage()->getDynamicVariable('wcf.user.notification.mail.header', array(
				'user' => $user
			))."\n\n";
			
			foreach ($events as $event) {
				$className = $eventObjects[$event['eventID']]->className;
				$class = new $className($eventObjects[$event['eventID']]);
				
				$class->setObject(
					$notificationObjects[$event['notificationID']],
					$objectTypes[$event['objectType']]['objects'][$event['objectID']],
					$authors[$event['authorID']],
					unserialize($event['additionalData'])
				);
				$class->setLanguage($user->getLanguage());
				
				if ($message != '') $message .= "\n\n";
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
			
			$message .= "\n\n".$user->getLanguage()->getDynamicVariable('wcf.user.notification.mail.daily.footer', array(
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
