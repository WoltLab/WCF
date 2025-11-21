<?php

namespace wcf\data\session;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\event\EventHandler;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * Executes session-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectAction<Session, SessionEditor>
 * @phpstan-ignore generics.notSubtype
 */
class SessionAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $allowGuestAccess = ['keepAlive'];

    /**
     * @inheritDoc
     */
    protected $className = SessionEditor::class;

    /**
     * list of data values returned upon a keep alive request
     * @var mixed[]
     */
    public $keepAliveData = [];

    /**
     * Validates the 'keepAlive' action.
     *
     * @return void
     */
    public function validateKeepAlive()
    {
        // does nothing
    }

    /**
     * Updates session's last activity time to prevent it from expiring. In addition this method
     * will return updated counters for notifications and 3rd party components.
     *
     * @return mixed[]
     * @deprecated 6.2 This method is a relic of the pre 5.4 session system and is no longer required.
     */
    public function keepAlive()
    {
        // update notification counts
        $this->keepAliveData = [
            'userNotificationCount' => UserNotificationHandler::getInstance()->getNotificationCount(true),
        ];

        // notify 3rd party components
        EventHandler::getInstance()->fireAction($this, 'keepAlive');

        return $this->keepAliveData;
    }

    /**
     * Validates parameters to poll notification data.
     *
     * @return void
     */
    public function validatePoll()
    {
        $this->readInteger('lastRequestTimestamp');
    }

    /**
     * Polls notification data, including values provided by `keepAlive()`.
     *
     * @return array{keepAliveData: array<string, mixed>, lastRequestTimestamp: int, pollData: array<string, mixed>}
     */
    public function poll()
    {
        $pollData = [];

        // trigger session keep alive
        $keepAliveData = (new self([], 'keepAlive'))->executeAction()['returnValues'];

        // get notifications
        if (!empty($keepAliveData['userNotificationCount'])) {
            // We can synchronize notification polling between tabs of the same domain, but
            // this doesn't work for different origins, that is different sub-domains that
            // belong to the same instance.
            //
            // Storing the time of the last request on the server has the benefit of avoiding
            // the same notification being presented to the client by different tabs.
            $lastRequestTime = UserStorageHandler::getInstance()->getField('__notification_lastRequestTime');
            if ($lastRequestTime === null || $lastRequestTime < $this->parameters['lastRequestTimestamp']) {
                $lastRequestTime = $this->parameters['lastRequestTimestamp'];
            }

            $pollData['notification'] = UserNotificationHandler::getInstance()->getLatestNotification($lastRequestTime);

            if (!empty($pollData['notification'])) {
                UserStorageHandler::getInstance()->update(
                    WCF::getUser()->userID,
                    '__notification_lastRequestTime',
                    (string)\TIME_NOW
                );
            }
        }

        // notify 3rd party components
        EventHandler::getInstance()->fireAction($this, 'poll', $pollData);

        WCF::getSession()->disableUpdate(true);

        return [
            'keepAliveData' => $keepAliveData,
            'lastRequestTimestamp' => \TIME_NOW,
            'pollData' => $pollData,
        ];
    }
}
