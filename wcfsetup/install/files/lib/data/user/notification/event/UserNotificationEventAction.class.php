<?php

namespace wcf\data\user\notification\event;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\request\RequestHandler;
use wcf\system\user\notification\event\ITestableUserNotificationEvent;
use wcf\system\user\notification\TestableUserNotificationEventHandler;
use wcf\system\WCF;

/**
 * Executes user notification event-related actions.
 *
 * @author  Marcel Werk, Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  UserNotificationEventEditor[]   getObjects()
 * @method  UserNotificationEventEditor getSingleObject()
 */
class UserNotificationEventAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $requireACP = ['testEvent'];

    /**
     * currently tested user notification event
     * @var UserNotificationEvent
     * @since   3.1
     */
    protected $userNotificationEvent;

    /**
     * @inheritDoc
     * @return  UserNotificationEvent
     */
    public function create()
    {
        /** @var UserNotificationEvent $event */
        $event = parent::create();

        if ($event->preset) {
            $sql = "INSERT INTO wcf1_user_notification_event_to_user
                                (userID, eventID, mailNotificationType)
                    SELECT      userID, ?, ?
                    FROM        wcf1_user";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([
                $event->eventID,
                $event->presetMailNotificationType,
            ]);
        }

        return $event;
    }

    /**
     * Validates the `testEvent` action.
     *
     * @throws  PermissionDeniedException
     * @throws  UserInputException
     * @since   3.1
     */
    public function validateTestEvent()
    {
        if (!ENABLE_DEVELOPER_TOOLS) {
            throw new PermissionDeniedException();
        }

        $this->readInteger('eventID');

        $this->userNotificationEvent = new UserNotificationEvent($this->parameters['eventID']);
        if (
            !$this->userNotificationEvent->eventID || !\is_subclass_of(
                $this->userNotificationEvent->className,
                ITestableUserNotificationEvent::class
            )
        ) {
            throw new UserInputException('eventID');
        }
    }

    /**
     * Tests a certain user notification event by returning all possible notifications.
     *
     * @return  array
     * @since   3.1
     */
    public function testEvent()
    {
        $events = [];

        $originalLanguage = WCF::getLanguage();

        // temporarily tell request handler that this no acp request to
        // avoid issues with links
        $reflectionClass = new \ReflectionClass(RequestHandler::class);
        $reflectionProperty = $reflectionClass->getProperty('isACPRequest');
        $reflectionProperty->setValue(RequestHandler::getInstance(), false);

        /**
         * Returns the output of an exception shown in the dialog.
         *
         * @param \Exception|\Throwable $e
         * @return  string
         */
        $getRenderedException = static function ($e) {
            \wcf\functions\exception\logThrowable($e);

            return $e->getMessage();
        };

        $errors = 0;
        $hasEmailSupport = false;

        foreach (TestableUserNotificationEventHandler::getInstance()->getUserNotificationEvents($this->userNotificationEvent) as $event) {
            WCF::setLanguage($event->getLanguage()->languageID);

            $eventData = ['description' => $event->getTestCaseDescription()];

            try {
                $eventData['title'] = $event->getTitle();
            } catch (\Throwable $e) {
                $eventData['titleException'] = $getRenderedException($e);
                $errors++;
            }

            try {
                $eventData['message'] = $event->getMessage();
            } catch (\Throwable $e) {
                $eventData['messageException'] = $getRenderedException($e);
                $errors++;
            }

            try {
                $eventData['link'] = $event->getLink();
            } catch (\Throwable $e) {
                $eventData['linkException'] = $getRenderedException($e);
                $errors++;
            }

            if ($event->supportsEmailNotification()) {
                $hasEmailSupport = true;

                try {
                    $eventData['dailyEmail'] = TestableUserNotificationEventHandler::getInstance()->getEmailBody(
                        $event,
                        'daily'
                    );
                } catch (\Throwable $e) {
                    $eventData['dailyEmailException'] = $getRenderedException($e);
                    $errors++;
                }

                // for instant emails, a notification can only be triggered once
                if ($event->getNotification()->timesTriggered == 1) {
                    try {
                        $eventData['instantEmail'] = TestableUserNotificationEventHandler::getInstance()->getEmailBody(
                            $event,
                            'instant'
                        );
                    } catch (\Throwable $e) {
                        $eventData['instantEmailException'] = $getRenderedException($e);
                        $errors++;
                    }
                }
            }

            $events[] = $eventData;
        }

        if ($errors && \ob_get_level()) {
            // discard any output generated before the exception occurred
            while (\ob_get_level()) {
                \ob_end_clean();
            }
        }

        WCF::setLanguage($originalLanguage->languageID);

        $template = WCF::getTPL()->fetch('devtoolsNotificationTestDialog', 'wcf', [
            'events' => $events,
            'errors' => $errors,
            'hasEmailSupport' => $hasEmailSupport,
        ]);

        // reset acp request value
        $reflectionProperty->setValue(RequestHandler::getInstance(), true);

        return [
            'eventID' => $this->userNotificationEvent->eventID,
            'template' => $template,
        ];
    }
}
