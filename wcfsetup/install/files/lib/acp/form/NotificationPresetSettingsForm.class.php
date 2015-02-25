<?php
namespace wcf\acp\form;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\notification\event\UserNotificationEvent;
use wcf\data\user\notification\event\UserNotificationEventEditor;
use wcf\form\AbstractForm;
use wcf\system\cache\builder\UserNotificationEventCacheBuilder;
use wcf\system\exception\UserInputException;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\WCF;

/**
 * Shows the notification preset settings form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	form.acp
 * @category	Community Framework
 */
class NotificationPresetSettingsForm extends AbstractForm {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.notificationPresetSettings';
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.user.canEditUser');
	
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
	 * true to apply change to existing users
	 * @var	boolean
	 */
	public $applyChangesToExistingUsers = 0;
	
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
	}
	
	/**
	 * @see	\wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['settings'])) $this->settings = $_POST['settings'];
		if (isset($_POST['applyChangesToExistingUsers'])) $this->applyChangesToExistingUsers = intval($_POST['applyChangesToExistingUsers']);
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
			$eventIDs = array();
			foreach ($this->events as $events) {
				foreach ($events as $event) {
					$eventIDs[] = $event->eventID;
					$this->settings[$event->eventID] = array(
						'enabled' => $event->preset,
						'mailNotificationType' => $event->presetMailNotificationType
					);
				}
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
			'settings' => $this->settings,
			'applyChangesToExistingUsers' => $this->applyChangesToExistingUsers
		));
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		foreach ($this->events as $objectType => $events) {
			foreach ($events as $event) {
				$preset = 0;
				$presetMailNotificationType = 'none';
				
				if (!empty($this->settings[$event->eventID]['enabled'])) {
					$preset = 1;
					if (isset($this->settings[$event->eventID]['mailNotificationType'])) {
						$presetMailNotificationType = $this->settings[$event->eventID]['mailNotificationType'];
					}
				}
				
				if ($event->preset != $preset || $event->presetMailNotificationType != $presetMailNotificationType) {
					$editor = new UserNotificationEventEditor(new UserNotificationEvent(null, array('eventID' => $event->eventID)));
					$editor->update(array(
						'preset' => $preset,
						'presetMailNotificationType' => $presetMailNotificationType
					));
					
					if ($this->applyChangesToExistingUsers) {
						if (!$preset) {
							$sql = "DELETE FROM	wcf".WCF_N."_user_notification_event_to_user
								WHERE		eventID = ?";
							$statement = WCF::getDB()->prepareStatement($sql);
							$statement->execute(array($event->eventID));
						}
						else {
							$sql = "REPLACE INTO	wcf".WCF_N."_user_notification_event_to_user
										(userID, eventID, mailNotificationType)
								SELECT		userID, ?, ?
								FROM		wcf".WCF_N."_user";
							$statement = WCF::getDB()->prepareStatement($sql);
							$statement->execute(array($event->eventID, $presetMailNotificationType));
						}
					}
				}
			}
		}
		UserNotificationEventCacheBuilder::getInstance()->reset();
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
}
