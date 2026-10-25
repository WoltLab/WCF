<?php

namespace wcf\system\endpoint\controller\core\users;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\command\user\Unfollow;
use wcf\data\user\User;
use wcf\http\Helper;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Lets the active user stop following the user with the given ID.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
#[PostRequest('/core/users/{id:\d+}/unfollow')]
final class UnfollowUser implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $this->assertUserIsLoggedIn();

        $user = Helper::fetchObjectFromRequestParameter($variables['id'], User::class);
        $this->assertTargetCanBeUnfollowed($user);

        new Unfollow(WCF::getUser(), $user)();

        return new JsonResponse([]);
    }

    private function assertUserIsLoggedIn(): void
    {
        if (WCF::getUser()->isGuest()) {
            throw new PermissionDeniedException();
        }
    }

    private function assertTargetCanBeUnfollowed(User $target): void
    {
        if ($target->userID === WCF::getUser()->userID) {
            throw new PermissionDeniedException();
        }
    }
}
