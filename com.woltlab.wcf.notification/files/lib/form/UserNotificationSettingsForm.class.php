<?php
// wcf imports
require_once(WCF_DIR.'lib/form/AbstractSecureForm.class.php');
require_once(WCF_DIR.'lib/page/util/menu/UserCPMenu.class.php');
require_once(WCF_DIR.'lib/data/user/notification/NotificationHandler.class.php');
require_once(WCF_DIR.'lib/data/user/NotificationUser.class.php');

/**
 * Shows the notification settings form.
 *
 * @author	Tim Düsterhus, Oliver Kliebisch
 * @copyright	2009-2010 Oliver Kliebisch
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.wcf.user.notification
 * @subpackage	form
 * @category 	Community Framework
 */
class UserNotificationSettingsForm extends AbstractSecureForm {
	/**
	 * @see AbstractPage::$templateName
	 */
	public $templateName = 'userNotificationSettings';

	/**
	 * An array holding all available notification object types
	 *
	 * @var array
	 */
	public $notificationObjectTypes = array();

	/**
	 * An array holding all available notification types
	 *
	 * @var array
	 */
	public $notificationTypes = array();

	/**
	 * The activated event notifications in the form
	 *
	 * @var array
	 */
	public $activeEventNotifications = array();

	/**
	 * The notification user object of the current user
	 *
	 * @var NotificationUser
	 */
	public $notificationUser = null;

	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();

		$this->notificationUser = new NotificationUser(null, WCF::getUser());
		$this->notificationObjectTypes = NotificationHandler::getAvailableNotificationObjectTypes();
		$this->notificationTypes = NotificationHandler::getAvailableNotificationTypes();
	}

	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();

		if (isset($_POST['activeEventNotifications']) && is_array($_POST['activeEventNotifications'])) $this->activeEventNotifications = $_POST['activeEventNotifications'];
	}

	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();

		// validate checked options and unset wrong settings
		foreach ($this->activeEventNotifications as $objectType => $events) {
			if (!isset($this->notificationObjectTypes[$objectType])) {
				unset ($this->activeEventNotifications[$objectType]);
				continue;
			}

			foreach ($events as $eventName => $notificationTypes) {
				if (!isset($this->notificationObjectTypes[$objectType]['events'][$eventName])) {
					unset ($this->activeEventNotifications[$objectType][$eventName]);
					continue;
				}

				foreach ($notificationTypes as $notificationType => $value) {
					if (!isset($this->notificationTypes[$notificationType])) {
						unset ($this->activeEventNotifications[$objectType][$eventName][$notificationType]);
						continue;
					}
				}
			}
		}

		$settings = array();
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_user_notification_event_settings
			WHERE packageID IN (".NotificationHandler::getAvailablePackageIDs().")";
		$result = WCF::getDB()->sendQuery($sql);
		while($row = WCF::getDB()->fetchArray($result)) {
			$settings[$row['objectType']][$row['eventName']][$row['notificationType']] = $row;
		}

		// add default values
		foreach ($this->notificationObjectTypes as $name => $objectType) {
			if (isset($objectType['events'])) {
				foreach ($objectType['events'] as $eventName => $event) {
					foreach ($this->notificationTypes as $typeName => $type) {
						if (!isset($this->activeEventNotifications[$name][$eventName]) || !isset($this->activeEventNotifications[$name][$eventName][$typeName])) {
							$this->activeEventNotifications[$name][$eventName][$typeName] = 0;
						}
						if (isset($settings[$name][$eventName][$typeName]) && !$settings[$name][$eventName][$typeName]['canBeDisabled']) {
							$this->activeEventNotifications[$name][$eventName][$typeName] = 1;
						}
						else if (isset($settings[$name][$eventName][$typeName]) && !$settings[$name][$eventName][$typeName]['enabled']) {
							$this->activeEventNotifications[$name][$eventName][$typeName] = 0;
						}
					}
				}
			}
		}
	}

	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();

		$settings = array();
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_user_notification_event_settings
			WHERE packageID IN (".NotificationHandler::getAvailablePackageIDs().")";
		$result = WCF::getDB()->sendQuery($sql);
		while($row = WCF::getDB()->fetchArray($result)) {
			$settings[$row['objectType']][$row['eventName']][$row['notificationType']] = $row;
		}

		if ($this->notificationTypes) {
			// calculate compatibility map
			foreach ($this->notificationObjectTypes as $name => $objectType) {
				if (!isset($objectType['events']) || !is_array($objectType['events'])) {
					unset ($this->notificationObjectTypes[$name]);
					continue;
				}
				foreach ($objectType['events'] as $eventName => $event) {
					$this->notificationObjectTypes[$name]['events'][$eventName]->supportedNotificationTypes = array();
					foreach ($this->notificationTypes as $typeName => $type) {
						$adminSettings = true;
						if (isset($settings[$name][$eventName][$typeName]) && !$settings[$name][$eventName][$typeName]['canBeDisabled']) $adminSettings = false;
						else if (isset($settings[$name][$eventName][$typeName]) && !$settings[$name][$eventName][$typeName]['enabled']) $adminSettings = false;
						$this->notificationObjectTypes[$name]['events'][$eventName]->supportedNotificationTypes[$typeName] = $event->supportsNotificationType($type) && $adminSettings;
					}
				}
			}
		}

		if (!count($_POST)) {
			foreach ($this->notificationObjectTypes as $name => $objectType) {
				if (isset($objectType['events'])) {
					foreach ($objectType['events'] as $eventName => $event) {
						foreach ($this->notificationTypes as $typeName => $type) {
							if (isset($this->notificationUser->eventNotificationSettings[$name][$eventName][$typeName])) {
								$this->activeEventNotifications[$name][$eventName][$typeName] = $this->notificationUser->eventNotificationSettings[$name][$eventName][$typeName];
							}
							else {
								if ($typeName == $event->defaultNotificationType) {
									$this->activeEventNotifications[$name][$eventName][$typeName] = 1;
								}
								else {
									$this->activeEventNotifications[$name][$eventName][$typeName] = 0;
								}
							}
							if (isset($settings[$name][$eventName][$typeName]) && !$settings[$name][$eventName][$typeName]['canBeDisabled']) {
								$this->activeEventNotifications[$name][$eventName][$typeName] = 1;
							}
							else if (isset($settings[$name][$eventName][$typeName]) && !$settings[$name][$eventName][$typeName]['enabled']) {
								$this->activeEventNotifications[$name][$eventName][$typeName] = 0;
							}
						}
					}
				}
			}
		}
	}

	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();

		WCF::getTPL()->assign(array(
			'notificationObjectTypes' => $this->notificationObjectTypes,
			'notificationTypes' => $this->notificationTypes,
			'activeEventNotifications' => $this->activeEventNotifications,
			'user' => $this->notificationUser
		));
	}

	/**
	 * @see Page::show()
	 */
	public function show() {
		// check module
		if (!MODULE_USER_NOTIFICATION) {
			throw new IllegalLinkException();
		}

		// check permission
		if (!WCF::getUser()->userID) {
			throw new PermissionDeniedException();
		}

		// set active user cp menu item
		UserCPMenu::getInstance()->setActiveMenuItem('wcf.user.usercp.menu.link.settings.notification');

		// show form
		parent::show();
	}

	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();

		// delete old data but only from this dependency tree
		$sql = "DELETE FROM	wcf".WCF_N."_user_notification_event_to_user
			WHERE		userID = ".WCF::getUser()->userID."
			AND		packageID IN (".NotificationHandler::getAvailablePackageIDs().")";
		WCF::getDB()->sendQuery($sql);

		$settings = array();
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_user_notification_event_settings
			WHERE packageID IN (".NotificationHandler::getAvailablePackageIDs().")";
		$result = WCF::getDB()->sendQuery($sql);
		while($row = WCF::getDB()->fetchArray($result)) {
			$settings[$row['objectType']][$row['eventName']][$row['notificationType']] = $row;
		}

		// prepare new data
		$inserts = '';
		foreach ($this->activeEventNotifications as $objectType => $events) {
			foreach ($events as $eventName => $notificationTypes) {
				foreach ($notificationTypes as $notificationType => $enabled) {
					if (isset($settings[$objectType][$eventName][$notificationType]) && !$settings[$objectType][$eventName][$notificationType]['enabled']) {
						$enabled = false;
					}
					else if (isset($settings[$objectType][$eventName][$notificationType]) && !$settings[$objectType][$eventName][$notificationType]['canBeDisabled']) {
						$enabled = true;
					}

					$objectTypeObject = NotificationHandler::getNotificationObjectTypeObject($objectType);
					if (!empty($inserts)) $inserts .= ',';
					$inserts .= "(".WCF::getUser()->userID.",
					".$objectTypeObject->getPackageID().",
					'".escapeString($objectType)."',
					'".escapeString($eventName)."',
					'".escapeString($notificationType)."',
					".($enabled ? "1" : "0").")";
				}
			}

		}

		if (!empty($inserts)) {
			$sql = "INSERT INTO	wcf".WCF_N."_user_notification_event_to_user
						(userID, packageID, objectType, eventName, notificationType, enabled)
						VALUES
						".$inserts;
			WCF::getDB()->sendQuery($sql);
		}

		$this->saved();

		// show success message
		WCF::getTPL()->assign('success', true);
	}

}
?>