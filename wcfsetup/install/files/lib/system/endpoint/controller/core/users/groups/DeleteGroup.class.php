<?php

namespace wcf\system\endpoint\controller\core\users\groups;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\user\group\UserGroup;
use wcf\data\user\group\UserGroupAction;
use wcf\http\Helper;
use wcf\system\endpoint\DeleteRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\PermissionDeniedException;

/**
 * Deletes the user group with the given ID.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
#[DeleteRequest("/core/users/groups/{id:\d+}")]
final class DeleteGroup implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $userGroup = Helper::fetchObjectFromRequestParameter($variables['id'], UserGroup::class);

        $this->assertGroupCanBeDeleted($userGroup);

        (new UserGroupAction([$userGroup], 'delete'))->executeAction();

        return new JsonResponse([]);
    }

    private function assertGroupCanBeDeleted(UserGroup $userGroup): void
    {
        if (!$userGroup->isDeletable()) {
            throw new PermissionDeniedException();
        }
    }
}
