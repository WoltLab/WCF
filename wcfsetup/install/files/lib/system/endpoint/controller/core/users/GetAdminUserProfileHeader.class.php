<?php

namespace wcf\system\endpoint\controller\core\users;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\user\User;
use wcf\data\user\UserProfile;
use wcf\http\Helper;
use wcf\system\endpoint\GetRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\view\user\profile\AdminUserProfileHeaderView;
use wcf\system\WCF;

/**
 * Retrieves the HTML code for the profile header of the user with the given ID
 * that is shown on the user edit page in the admin panel.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
#[GetRequest('/core/users/{id:\d+}/admin-profile-header')]
final class GetAdminUserProfileHeader implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        if (!WCF::getSession()->hasPermission('admin.user.canEditUser')) {
            throw new PermissionDeniedException();
        }

        $user = Helper::fetchObjectFromRequestParameter($variables['id'], User::class);

        if (!$user->canEdit()) {
            throw new PermissionDeniedException();
        }

        return new JsonResponse([
            'template' => (string)new AdminUserProfileHeaderView(new UserProfile($user)),
        ]);
    }
}
