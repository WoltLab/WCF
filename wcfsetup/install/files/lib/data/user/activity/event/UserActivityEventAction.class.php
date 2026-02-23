<?php

namespace wcf\data\user\activity\event;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\box\Box;
use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\system\box\RecentActivityListBoxController;
use wcf\system\exception\UserInputException;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\user\UserProfileHandler;
use wcf\system\WCF;

/**
 * Executes user activity event-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectAction<UserActivityEvent, UserActivityEventEditor>
 */
class UserActivityEventAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    public $allowGuestAccess = ['load'];

    /**
     * @var RecentActivityListBoxController
     */
    public $boxController;

    /**
     * Validates parameters to load recent activity entries.
     *
     * @return void
     * @deprecated 6.3
     */
    public function validateLoad()
    {
        $this->readInteger('boxID', true);
        $this->readBoolean('filteredByFollowedUsers', true);
        $this->readInteger('lastEventTime');
        $this->readInteger('lastEventID', true);
        $this->readInteger('userID', true);

        if ($this->parameters['boxID']) {
            $box = new Box($this->parameters['boxID']);
            if ($box->boxID) {
                $boxController = $box->getController();
                if ($boxController instanceof RecentActivityListBoxController) {
                    // all checks passed, end validation; otherwise throw the exception below
                    $this->boxController = $boxController;

                    return;
                }
            }

            throw new UserInputException('boxID');
        }
    }

    /**
     * Loads a list of recent activity entries.
     *
     * @return array{
     *  lastEventID: int,
     *  lastEventTime: int,
     *  template: string,
     * }|array{}
     * @deprecated 6.3 Use `RenderUserActivityEvents` endpoint instead.
     */
    public function load()
    {
        if ($this->boxController !== null) {
            $eventList = $this->boxController->getFilteredList();
        } else {
            $eventList = new ViewableUserActivityEventList();

            // profile view
            if ($this->parameters['userID']) {
                $eventList->getConditionBuilder()->add("user_activity_event.userID = ?", [$this->parameters['userID']]);
            } else {
                if ($this->parameters['filteredByFollowedUsers'] && \count(UserProfileHandler::getInstance()->getFollowingUsers())) {
                    $eventList->getConditionBuilder()->add(
                        'user_activity_event.userID IN (?)',
                        [UserProfileHandler::getInstance()->getFollowingUsers()]
                    );
                }
            }
        }

        if ($this->parameters['lastEventID']) {
            $eventList->getConditionBuilder()->add(
                "user_activity_event.time <= ?",
                [$this->parameters['lastEventTime']]
            );
            $eventList->getConditionBuilder()->add(
                "user_activity_event.eventID < ?",
                [$this->parameters['lastEventID']]
            );
        } else {
            $eventList->getConditionBuilder()->add(
                "user_activity_event.time < ?",
                [$this->parameters['lastEventTime']]
            );
        }

        $eventList->readObjects();
        $lastEventTime = $eventList->getLastEventTime();

        if (!$lastEventTime) {
            return [];
        }

        // removes orphaned and non-accessible events
        UserActivityEventHandler::validateEvents($eventList);

        if ($this->boxController !== null) {
            $eventList->truncate($this->boxController->getBox()->limit);
        }

        if (!\count($eventList)) {
            return [];
        }

        // parse template
        $events = $eventList->getObjects();

        return [
            'lastEventID' => \end($events)->eventID,
            'lastEventTime' => $lastEventTime,
            'template' => WCF::getTPL()->render('wcf', 'recentActivityListItem', [
                'eventList' => $eventList,
            ]),
        ];
    }

    /**
     * Does nothing.
     *
     * @return void
     */
    public function validateSwitchContext() {}

    /**
     * @return void
     */
    public function switchContext()
    {
        /** @noinspection PhpUndefinedFieldInspection */
        $userAction = new UserAction([WCF::getUser()], 'update', [
            'options' => [
                User::getUserOptionID('recentActivitiesFilterByFollowing') => WCF::getUser()->recentActivitiesFilterByFollowing ? 0 : 1,
            ],
        ]);
        $userAction->executeAction();
    }
}
