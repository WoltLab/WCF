<?php

namespace wcf\system\endpoint\controller\core\users;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\command\user\Follow;
use wcf\data\user\User;
use wcf\http\Helper;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Lets the active user follow the user with the given ID.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
#[PostRequest('/core/users/{id:\d+}/follow')]
final class FollowUser implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $this->assertUserIsLoggedIn();

        $user = Helper::fetchObjectFromRequestParameter($variables['id'], User::class);
        $this->assertTargetCanBeFollowed($user);
        $this->assertUserIsNotIgnored($user);

        new Follow(WCF::getUser(), $user)();

        return new JsonResponse([]);
    }

    private function assertUserIsLoggedIn(): void
    {
        if (WCF::getUser()->isGuest()) {
            throw new PermissionDeniedException();
        }
    }

    private function assertTargetCanBeFollowed(User $target): void
    {
        if ($target->userID === WCF::getUser()->userID) {
            throw new PermissionDeniedException();
        }
    }

    private function assertUserIsNotIgnored(User $target): void
    {
        $sql = "SELECT  ignoreID
                FROM    wcf1_user_ignore
                WHERE   userID = ?
                    AND ignoreUserID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $target->userID,
            WCF::getUser()->userID,
        ]);

        if ($statement->fetchSingleColumn() !== false) {
            throw new PermissionDeniedException();
        }
    }
}
