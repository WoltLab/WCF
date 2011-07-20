<?php
namespace wcf\system\user\notification;
use wcf\data\user\notification\event\UserNotificationEvent;
use wcf\data\user\notification\object\type\UserNotificationObjectType;
use wcf\data\user\notification\recipient\UserNotificationRecipientList;
use wcf\data\user\notification\UserNotificationAction;
use wcf\system\cache\CacheHandler;
use wcf\system\exception\SystemException;
use wcf\system\user\notification\object\UserNotificationObject;
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
	 * @param	wcf\system\user\notification\object\UserNotificationObject	$notificationObject
	 * @param	array<integer>							$recipientIDs
	 * @param	array<mixed>							$additionalData
	 */
	public function fireEvent($eventName, $objectType, UserNotificationObject $notificationObject, array $recipientIDs, array $additionalData = array()) {
		// check given object type and event name
		if (!isset($this->availableObjectTypes[$objectType]['events'][$eventName])) {
			throw new SystemException("Unknown event '.$objectType.'-.$eventName.' given");
		}
		
		// get objects
		$objectTypeData = $this->availableObjectTypes[$objectType]['object'];
		$eventData = $this->availableObjectTypes[$objectType]['events'][$eventName];
		
		// save notification
		$action = new UserNotificationAction(array(), 'create', array(
			'packageID' => PACKAGE_ID,
			'eventID' => $eventData->eventID,
			'objectID' => $notificationObject->getObjectID(),
			'time' => TIME_NOW,
			'shortOutput' => $eventData->getObject()->getShortOutput($eventName),
			'mediumOutput' => $eventData->getObject()->getMediumOutput($eventName),
			'longOutput' => $eventData->getObject()->getOutput($eventName),
			'additionalData' => serialize($additionalData),
			'recipientIDs' => $recipientIDs
		));
		$notification = $action->executeAction();
		
		// get recipients
		$recipientList = new UserNotificationRecipientList();
		$recipientList->getConditionBuilder()->add('user_table.userID = ?', array($recipientIDs));
		$recipientList->readObjects();
		
		// sends notifications
		foreach ($recipientList->getObjects() as $recipient) {
			foreach ($recipient->getNotificationTypes($eventData->eventID) as $notificationType) {
				if ($eventData->getObject()->supportsNotificationType($notificationType)) {
					$notificationType->getObject()->send($notification, $recipient, $eventData);
				}
			}
		}
	}
}
