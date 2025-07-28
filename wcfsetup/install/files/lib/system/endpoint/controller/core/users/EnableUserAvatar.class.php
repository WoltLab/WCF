<?php

namespace wcf\system\endpoint\controller\core\users;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\user\group\UserGroup;
use wcf\data\user\User;
use wcf\http\Helper;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\user\command\EnableAvatar;
use wcf\system\WCF;

/**
 * API endpoint for enabling a user's avatar.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
#[PostRequest('/core/users/{id:\d+}/enable-avatar')]
final class EnableUserAvatar implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $user = Helper::fetchObjectFromRequestParameter($variables['id'], User::class);

        $this->assertAvatarCanBeEnabled($user);

        if ($user->disableAvatar) {
            (new EnableAvatar($user))();
        }

        return new JsonResponse([]);
    }

    private function assertAvatarCanBeEnabled(User $user): void
    {
        if (WCF::getUser()->userID === $user->userID) {
            throw new IllegalLinkException();
        }
        if (!WCF::getSession()->getPermission('admin.user.canDisableAvatar')) {
            throw new PermissionDeniedException();
        }
        if (!UserGroup::isAccessibleGroup($user->getGroupIDs())) {
            throw new PermissionDeniedException();
        }
    }
}
