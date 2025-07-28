<?php

namespace wcf\system\endpoint\controller\core\users;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\user\UserProfile;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\endpoint\GetRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\view\user\profile\UserProfileHeaderView;
use wcf\system\WCF;

/**
 * API endpoint for getting the header template for a user profile.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
#[GetRequest('/core/users/{id:\d+}/profile-header')]
final class GetUserProfileHeader implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $userProfile = UserProfileRuntimeCache::getInstance()->getObject((int)$variables['id']);

        $this->assertUserProfileCanBeViewed($userProfile);

        $view = new UserProfileHeaderView($userProfile);

        return new JsonResponse([
            'template' => $view->__toString(),
        ]);
    }

    private function assertUserProfileCanBeViewed(?UserProfile $userProfile): void
    {
        if ($userProfile === null) {
            throw new UserInputException('id');
        }

        if ($userProfile->userID !== WCF::getUser()->userID && !WCF::getSession()->getPermission('user.profile.canViewUserProfile')) {
            throw new PermissionDeniedException();
        }
    }
}
