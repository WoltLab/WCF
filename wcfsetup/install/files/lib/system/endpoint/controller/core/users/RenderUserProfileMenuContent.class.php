<?php

namespace wcf\system\endpoint\controller\core\users;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\user\User;
use wcf\http\Helper;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\endpoint\GetRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\menu\user\profile\UserProfileMenu;
use wcf\system\WCF;

/**
 * Renders the content of a user profile menu item for the user with the given ID.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
#[GetRequest('/core/users/{id:\d+}/profile-menu-content')]
final class RenderUserProfileMenuContent implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $parameters = Helper::mapApiParameters($request, RenderUserProfileMenuContentParameters::class);

        $user = Helper::fetchObjectFromRequestParameter($variables['id'], User::class);

        $menuItem = UserProfileMenu::getInstance()->getMenuItem($parameters->menuItem);
        if ($menuItem === null) {
            throw new IllegalLinkException();
        }

        $contentManager = $menuItem->getContentManager();
        if (!$contentManager->isVisible($user->userID)) {
            throw new PermissionDeniedException();
        }

        $userProfile = UserProfileRuntimeCache::getInstance()->getObject($user->userID);
        if (
            $userProfile->userID !== WCF::getUser()->userID
            && !WCF::getSession()->hasPermission('user.profile.canViewUserProfile')
        ) {
            throw new PermissionDeniedException();
        }

        if ($userProfile->isProtected()) {
            throw new PermissionDeniedException();
        }

        return new JsonResponse([
            'template' => $contentManager->getContent($user->userID),
        ]);
    }
}

/** @internal */
final class RenderUserProfileMenuContentParameters
{
    public function __construct(
        /** @var non-empty-string */
        public readonly string $menuItem,
    ) {}
}
