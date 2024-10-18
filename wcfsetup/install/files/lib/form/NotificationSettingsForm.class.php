<?php

namespace wcf\form;

use wcf\data\object\type\ObjectTypeCache;
use wcf\system\exception\UserInputException;
use wcf\system\menu\user\UserMenu;
use wcf\system\user\notification\event\IUserNotificationEvent;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\WCF;

/**
 * Shows the notification settings form.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class NotificationSettingsForm extends AbstractForm
{
    /**
     * @inheritDoc
     */
    public $loginRequired = true;

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
     * @inheritDoc
     */
    public function readFormParameters()
    {
        parent::readFormParameters();

        if (isset($_POST['settings'])) {
            $this->settings = $_POST['settings'];
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
                !isset($settings['mailNotificationType'])
                || !\in_array($settings['mailNotificationType'], self::$validMailNotificationTypes)
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
            // get user settings
            foreach ($this->events as $events) {
                foreach ($events as $event) {
                    $this->settings[$event->eventID] = [
                        'enabled' => false,
                        'mailNotificationType' => 'none',
                    ];
                }
            }

            // get activation state
            $sql = "SELECT  eventID, mailNotificationType
                    FROM    wcf1_user_notification_event_to_user
                    WHERE   userID = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([WCF::getUser()->userID]);
            while ($row = $statement->fetchArray()) {
                $this->settings[$row['eventID']]['enabled'] = true;
                $this->settings[$row['eventID']]['mailNotificationType'] = $row['mailNotificationType'];
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
            $objectTypeObj = ObjectTypeCache::getInstance()
                ->getObjectTypeByName('com.woltlab.wcf.notification.objectType', $objectType);
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
        ]);
    }

    /**
     * @inheritDoc
     */
    public function show()
    {
        // set active tab
        UserMenu::getInstance()->setActiveMenuItem('wcf.user.menu.settings.notification');

        parent::show();
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        parent::save();

        $this->updateActivationStates();
        $this->saved();

        // show success message
        WCF::getTPL()->assign('success', true);
    }

    /**
     * Updates preferences for notification events.
     */
    protected function updateActivationStates()
    {
        $sql = "DELETE FROM wcf1_user_notification_event_to_user
                WHERE       eventID = ?
                        AND userID = ?";
        $statement = WCF::getDB()->prepare($sql);
        WCF::getDB()->beginTransaction();
        $newSettings = [];
        foreach ($this->settings as $eventID => $setting) {
            $statement->execute([
                $eventID,
                WCF::getUser()->userID,
            ]);

            if ($setting['enabled']) {
                $newSettings[] = [
                    'eventID' => $eventID,
                    'mailNotificationType' => $setting['mailNotificationType'],
                ];
            }
        }

        if (!empty($newSettings)) {
            $sql = "INSERT INTO wcf1_user_notification_event_to_user
                                (eventID, userID, mailNotificationType)
                    VALUES      (?, ?, ?)";
            $statement = WCF::getDB()->prepare($sql);
            foreach ($newSettings as $newSetting) {
                $statement->execute([
                    $newSetting['eventID'],
                    WCF::getUser()->userID,
                    $newSetting['mailNotificationType'],
                ]);
            }
        }
        WCF::getDB()->commitTransaction();
    }
}
