<?php

namespace wcf\system\endpoint\controller\core\users\groups\assignments;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\command\user\group\assignment\DeleteUserGroupAssignment;
use wcf\data\user\group\assignment\UserGroupAssignment;
use wcf\http\Helper;
use wcf\system\endpoint\DeleteRequest;
use wcf\system\endpoint\IController;
use wcf\system\WCF;

/**
 * Deletes the user group assignments with the given ID.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
#[DeleteRequest("/core/users/groups/assignments/{id:\d+}")]
final class DeleteAssignment implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $this->assertAssignmentCanBeDeleted();

        $assignment = Helper::fetchObjectFromRequestParameter($variables['id'], UserGroupAssignment::class);

        new DeleteUserGroupAssignment($assignment)();

        return new JsonResponse([]);
    }

    private function assertAssignmentCanBeDeleted(): void
    {
        WCF::getSession()->checkPermissions(['admin.user.canManageGroupAssignment']);
    }
}
