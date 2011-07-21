<?php
namespace wcf\system\user\notification;
use wcf\data\user\notification\event\UserNotificationEvent;
use wcf\data\user\notification\recipient\UserNotificationRecipientList;
use wcf\data\user\notification\UserNotificationAction;
use wcf\system\cache\CacheHandler;
use wcf\system\exception\SystemException;
use wcf\system\user\notification\object\IUserNotificationObject;
use wcf\system\SingletonFactory;

/**
 * Handles user notifications.
 *
 * @author	Marcel Werk, Oliver Kliebisch
 * @copyright	2001-2011 WoltLab GmbH, Oliver Kliebisch
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.notification
 * @subpackage	system.user.notification
 * @category 	Community Framework
 */
class UserNotificationHandler extends SingletonFactory {
	/**
	 * list of available object types
	 * @var array
	 */
	protected $availableObjectTypes = array();
	
	/**
	 * @see wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		// load cache
		CacheHandler::getInstance()->addResource('user-notification-object-type-'.PACKAGE_ID, WCF_DIR.'cache/cache.user-notification-object-type-'.PACKAGE_ID.'.php', 'wcf\system\cache\CacheBuilderUserNotificationObjectType');
		$this->availableObjectTypes = CacheHandler::getInstance()->get('user-notification-object-type-'.PACKAGE_ID);
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
		if (!isset($this->availableObjectTypes[$objectType]['events'][$eventName])) {
			throw new SystemException("Unknown event '.$objectType.'-.$eventName.' given");
		}
		
		// get objects
		$objectType = $this->availableObjectTypes[$objectType]['object'];
		$event = $this->availableObjectTypes[$objectType]['events'][$eventName];
		
		// save notification
		$action = new UserNotificationAction(array(), 'create', array('data' => array(
			'packageID' => PACKAGE_ID,
			'eventID' => $event->eventID,
			'objectID' => $notificationObject->getObjectID(),
			'time' => TIME_NOW,
			'shortOutput' => $event->getShortOutput($eventName),
			'mediumOutput' => $event->getMediumOutput($eventName),
			'longOutput' => $event->getOutput($eventName),
			'additionalData' => serialize($additionalData),
			'recipientIDs' => $recipientIDs
		)));
		$result = $action->executeAction();
		$notification = $result['returnValues'];
		
		// get recipients
		$recipientList = new UserNotificationRecipientList();
		$recipientList->getConditionBuilder()->add('user.userID = ?', array($recipientIDs));
		$recipientList->readObjects();
		
		// sends notifications
		foreach ($recipientList->getObjects() as $recipient) {
			foreach ($recipient->getNotificationTypes($event->eventID) as $notificationType) {
				if ($event->supportsNotificationType($notificationType)) {
					$notificationType->send($notification, $recipient, $event);
				}
			}
		}
	}
}
