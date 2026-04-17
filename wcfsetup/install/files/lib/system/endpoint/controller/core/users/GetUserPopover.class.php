<?php

namespace wcf\system\endpoint\controller\core\users;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\user\User;
use wcf\data\user\UserProfile;
use wcf\http\Helper;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\endpoint\GetRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\interaction\InteractionContextMenuComponent;
use wcf\system\interaction\user\UserProfileInteractions;
use wcf\system\WCF;

/**
 * Retrieves the HTML code for the popover of the user with the given ID.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
#[GetRequest('/core/users/{id:\d+}/popover')]
final class GetUserPopover implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        if (!WCF::getSession()->hasPermission('user.profile.canViewUserProfile')) {
            throw new PermissionDeniedException();
        }

        $user = Helper::fetchObjectFromRequestParameter($variables['id'], User::class);

        $userProfile = UserProfileRuntimeCache::getInstance()->getObject($user->userID);

        return new JsonResponse([
            'template' => $this->renderPopover($userProfile),
        ]);
    }

    private function renderPopover(UserProfile $user): string
    {
        $interactionContextMenuComponent = new InteractionContextMenuComponent(
            new UserProfileInteractions()
        );

        return WCF::getTPL()->render('wcf', 'userPopover', [
            'user' => $user,
            'contextMenuButton' => $interactionContextMenuComponent->renderButton($user),
            'interactionInitialization' => $interactionContextMenuComponent->renderInitialization('userPopover_' . $user->userID),
        ]);
    }
}
