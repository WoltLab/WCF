<?php

namespace wcf\system\endpoint\controller\core\users\activityEvents;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\command\user\SetRecentActivitiesFilterByFollowing;
use wcf\http\Helper;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Sets whether the recent activities shown to the active user are filtered by
 * the users they follow.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
#[PostRequest('/core/users/activity-events/filter')]
final class SetUserActivityEventFilter implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        if (WCF::getUser()->isGuest()) {
            throw new PermissionDeniedException();
        }

        $parameters = Helper::mapApiParameters($request, SetUserActivityEventFilterParameters::class);

        new SetRecentActivitiesFilterByFollowing(WCF::getUser(), $parameters->filteredByFollowedUsers)();

        return new JsonResponse([]);
    }
}

/** @internal */
final class SetUserActivityEventFilterParameters
{
    public function __construct(
        public readonly bool $filteredByFollowedUsers,
    ) {}
}
