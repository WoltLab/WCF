<?php

namespace wcf\system\endpoint\controller\core\users\activityEvents;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\box\Box;
use wcf\data\user\activity\event\ViewableUserActivityEventList;
use wcf\http\Helper;
use wcf\system\box\RecentActivityListBoxController;
use wcf\system\endpoint\GetRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\UserInputException;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\user\UserProfileHandler;
use wcf\system\WCF;

/**
 * Retrieves the HTML code for the rendering of recent user activity events.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
#[GetRequest('/core/users/activity-events/render')]
final class RenderUserActivityEvents implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $parameters = Helper::mapApiParameters($request, RenderUserActivityEventsParameters::class);

        $boxController = null;
        if ($parameters->boxID !== 0) {
            $box = new Box($parameters->boxID);
            if (!$box->boxID) {
                throw new UserInputException('boxID');
            }

            $controller = $box->getController();
            if (!$controller instanceof RecentActivityListBoxController) {
                throw new UserInputException('boxID');
            }

            $boxController = $controller;
        }

        return new JsonResponse($this->render(
            $parameters->lastEventTime,
            $parameters->lastEventID,
            $parameters->userID,
            $parameters->filteredByFollowedUsers,
            $boxController,
        ));
    }

    /**
     * @return array{
     *  lastEventID: int,
     *  lastEventTime: int,
     *  template: string,
     * }|array{}
     */
    private function render(
        int $lastEventTime,
        int $lastEventID = 0,
        int $userID = 0,
        bool $filteredByFollowedUsers = false,
        ?RecentActivityListBoxController $boxController = null,
    ): array {
        if ($boxController !== null) {
            $eventList = $boxController->getFilteredList();
        } else {
            $eventList = new ViewableUserActivityEventList();

            if ($userID) {
                $eventList->getConditionBuilder()->add(
                    "user_activity_event.userID = ?",
                    [$userID]
                );
            } elseif (
                $filteredByFollowedUsers
                && \count(UserProfileHandler::getInstance()->getFollowingUsers())
            ) {
                $eventList->getConditionBuilder()->add(
                    'user_activity_event.userID IN (?)',
                    [UserProfileHandler::getInstance()->getFollowingUsers()]
                );
            }
        }

        if ($lastEventID) {
            $eventList->getConditionBuilder()->add(
                "user_activity_event.time <= ?",
                [$lastEventTime]
            );
            $eventList->getConditionBuilder()->add(
                "user_activity_event.eventID < ?",
                [$lastEventID]
            );
        } else {
            $eventList->getConditionBuilder()->add(
                "user_activity_event.time < ?",
                [$lastEventTime]
            );
        }

        $eventList->readObjects();
        $lastEventTime = $eventList->getLastEventTime();

        if (!$lastEventTime) {
            return [];
        }

        UserActivityEventHandler::validateEvents($eventList);

        if ($boxController !== null) {
            $eventList->truncate($boxController->getBox()->limit);
        }

        if (!\count($eventList)) {
            return [];
        }

        $events = $eventList->getObjects();

        return [
            'lastEventID' => \end($events)->eventID,
            'lastEventTime' => $lastEventTime,
            'template' => WCF::getTPL()->render('wcf', 'recentActivityListItem', [
                'eventList' => $eventList,
            ]),
        ];
    }
}

/** @internal */
final class RenderUserActivityEventsParameters
{
    public function __construct(
        public readonly int $lastEventTime,
        public readonly int $lastEventID = 0,
        public readonly int $userID = 0,
        public readonly int $boxID = 0,
        public readonly bool $filteredByFollowedUsers = false,
    ) {}
}
