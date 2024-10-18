<?php

namespace wcf\acp\form;

use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\notification\event\UserNotificationEvent;
use wcf\data\user\notification\event\UserNotificationEventEditor;
use wcf\form\AbstractForm;
use wcf\system\cache\builder\UserNotificationEventCacheBuilder;
use wcf\system\exception\UserInputException;
use wcf\system\user\notification\event\IUserNotificationEvent;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\WCF;

/**
 * Shows the notification preset settings form.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class NotificationPresetSettingsForm extends AbstractForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.notificationPresetSettings';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.user.canManageNotificationSettings'];

    /**
     * list of notification events
     * @var IUserNotificationEvent[][]
     */
    public $events;

    /**
     * list of settings by event
     * @var mixed[][]
     */
    public $settings = [];

    /**
     * true to apply change to existing users
     * @var bool
     */
    public $applyChangesToExistingUsers = 0;

    /**
     * list of valid options for the mail notification type.
     * @var string[]
     */
    protected static $validMailNotificationTypes = ['none', 'instant', 'daily'];

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        $this->events = UserNotificationHandler::getInstance()->getAvailableEvents();
    }

    /**
     * @inheritDoc
     */
    public function readFormParameters()
    {
        parent::readFormParameters();

        if (isset($_POST['settings'])) {
            $this->settings = $_POST['settings'];
        }
        if (isset($_POST['applyChangesToExistingUsers'])) {
            $this->applyChangesToExistingUsers = \intval($_POST['applyChangesToExistingUsers']);
        }
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        parent::validate();

        // valid event ids
        $validEventIDs = [];
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
            if (!\in_array($eventID, $validEventIDs)) {
                throw new UserInputException();
            }

            // ensure 'enabled' exists
            if (!isset($settings['enabled'])) {
                $settings['enabled'] = 0;
            }

            // ensure 'mailNotificationType' exists
            if (
                !isset($settings['mailNotificationType']) || !\in_array(
                    $settings['mailNotificationType'],
                    self::$validMailNotificationTypes
                )
            ) {
                $settings['mailNotificationType'] = 'none';
            }
        }
        unset($settings);
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        // default values
        if (empty($_POST)) {
            foreach ($this->events as $events) {
                foreach ($events as $event) {
                    $this->settings[$event->eventID] = [
                        'enabled' => $event->preset,
                        'mailNotificationType' => $event->presetMailNotificationType,
                    ];
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        $groupedEvents = [];
        foreach ($this->events as $objectType => $events) {
            $objectTypeObj = ObjectTypeCache::getInstance()->getObjectTypeByName(
                'com.woltlab.wcf.notification.objectType',
                $objectType
            );
            $category = ($objectTypeObj->category ?: $objectType);

            if (!isset($groupedEvents[$category])) {
                $groupedEvents[$category] = [];
            }

            foreach ($events as $event) {
                $groupedEvents[$category][] = $event;
            }
        }

        \ksort($groupedEvents);

        WCF::getTPL()->assign([
            'events' => $groupedEvents,
            'settings' => $this->settings,
            'applyChangesToExistingUsers' => $this->applyChangesToExistingUsers,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        parent::save();

        foreach ($this->events as $events) {
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
                    $editor = new UserNotificationEventEditor(new UserNotificationEvent(
                        null,
                        ['eventID' => $event->eventID]
                    ));
                    $editor->update([
                        'preset' => $preset,
                        'presetMailNotificationType' => $presetMailNotificationType,
                    ]);

                    if ($this->applyChangesToExistingUsers) {
                        if (!$preset) {
                            $sql = "DELETE FROM wcf1_user_notification_event_to_user
                                    WHERE       eventID = ?";
                            $statement = WCF::getDB()->prepare($sql);
                            $statement->execute([$event->eventID]);
                        } else {
                            $sql = "REPLACE INTO    wcf1_user_notification_event_to_user
                                                    (userID, eventID, mailNotificationType)
                                    SELECT          userID, ?, ?
                                    FROM            wcf1_user";
                            $statement = WCF::getDB()->prepare($sql);
                            $statement->execute([$event->eventID, $presetMailNotificationType]);
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
