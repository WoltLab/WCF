<?php
namespace wcf\form;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\exception\UserInputException;
use wcf\system\menu\user\UserMenu;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\WCF;

/**
 * Shows the notification settings form.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	form
 * @category	Community Framework
 */
class NotificationSettingsForm extends AbstractForm {
	/**
	 * @see	\wcf\page\AbstractPage::$loginRequired
	 */
	public $loginRequired = true;
	
	/**
	 * list of notification events
	 * @var	array<array>
	 */
	public $events = null;
	
	/**
	 * list of settings by event
	 * @var	array<array>
	 */
	public $settings = array();
	
	/**
	 * list of valid options for the mail notification type.
	 * @var	array<string>
	 */
	protected static $validMailNotificationTypes = array('none', 'instant', 'daily');
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->events = UserNotificationHandler::getInstance()->getAvailableEvents();
		
		// filter events
		foreach ($this->events as $objectTypeID => $events) {
			foreach ($events as $eventName => $event) {
				if (!$event->isVisible()) {
					unset($this->events[$objectTypeID][$eventName]);
				}
			}
			
			if (empty($this->events[$objectTypeID])) {
				unset($this->events[$objectTypeID]);
			}
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['settings'])) $this->settings = $_POST['settings'];
	}
	
	/**
	 * @see	\wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
		// valid event ids
		$validEventIDs = array();
		foreach ($this->events as $events) {
			foreach ($events as $event) {
				$validEventIDs[] = $event->eventID;
				
				if (!isset($this->settings[$event->eventID]['enabled'])) {
					$this->settings[$event->eventID]['enabled'] = 0;
				}
			}
		}
		
		foreach ($this->settings as $eventID => &$settings) {
			// validate event id
			if (!in_array($eventID, $validEventIDs)) {
				throw new UserInputException();
			}
			
			// ensure 'enabled' exists
			if (!isset($settings['enabled'])) {
				$settings['enabled'] = 0;
			}
			
			// ensure 'mailNotificationType' exists
			if (!isset($settings['mailNotificationType']) || !in_array($settings['mailNotificationType'], self::$validMailNotificationTypes)) {
				$settings['mailNotificationType'] = 'none';
			}
		}
		unset($settings);
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		// default values
		if (empty($_POST)) {
			// get user settings
			$eventIDs = array();
			foreach ($this->events as $events) {
				foreach ($events as $event) {
					$eventIDs[] = $event->eventID;
					$this->settings[$event->eventID] = array(
						'enabled' => false,
						'mailNotificationType' => 'none'
					);
				}
			}
			
			// get activation state
			$sql = "SELECT	eventID, mailNotificationType
				FROM	wcf".WCF_N."_user_notification_event_to_user
				WHERE	userID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array(WCF::getUser()->userID));
			while ($row = $statement->fetchArray()) {
				$this->settings[$row['eventID']]['enabled'] = true;
				$this->settings[$row['eventID']]['mailNotificationType'] = $row['mailNotificationType'];
			}
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		$groupedEvents = array();
		foreach ($this->events as $objectType => $events) {
			$objectTypeObj = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.notification.objectType', $objectType);
			$category = ($objectTypeObj->category ?: $objectType);
			
			if (!isset($groupedEvents[$category])) {
				$groupedEvents[$category] = array();
			}
			
			foreach ($events as $event) $groupedEvents[$category][] = $event;
		}
		
		ksort($groupedEvents);
		
		WCF::getTPL()->assign(array(
			'events' => $groupedEvents,
			'settings' => $this->settings
		));
	}
	
	/**
	 * @see	\wcf\page\IPage::show()
	 */
	public function show() {
		// set active tab
		UserMenu::getInstance()->setActiveMenuItem('wcf.user.menu.settings.notification');
		
		parent::show();
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		$this->updateActivationStates();
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * Updates preferences for notification events.
	 */
	protected function updateActivationStates() {
		$sql = "DELETE FROM	wcf".WCF_N."_user_notification_event_to_user
			WHERE		eventID = ?
					AND userID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		WCF::getDB()->beginTransaction();
		$newSettings = array();
		foreach ($this->settings as $eventID => $setting) {
			$statement->execute(array(
				$eventID,
				WCF::getUser()->userID
			));
			
			if ($setting['enabled']) {
				$newSettings[] = array(
					'eventID' => $eventID,
					'mailNotificationType' => $setting['mailNotificationType']
				);
			}
		}
		
		if (!empty($newSettings)) {
			$sql = "INSERT INTO	wcf".WCF_N."_user_notification_event_to_user
						(eventID, userID, mailNotificationType)
				VALUES		(?, ?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			foreach ($newSettings as $newSetting) {
				$statement->execute(array(
					$newSetting['eventID'],
					WCF::getUser()->userID,
					$newSetting['mailNotificationType']
				));
			}
		}
		WCF::getDB()->commitTransaction();
	}
}
