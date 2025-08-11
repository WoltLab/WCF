<?php

namespace wcf\system\endpoint\controller\core\users\groups\assignment;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\command\user\group\assignment\DisableUserGroupAssignment;
use wcf\data\user\group\assignment\UserGroupAssignment;
use wcf\http\Helper;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\WCF;

/**
 * Disables the user group assignments with the given ID.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
#[PostRequest("/core/users/groups/assignments/{id:\d+}/disable")]
final class DisableAssignment implements IController
{
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $assignment = Helper::fetchObjectFromRequestParameter($variables['id'], UserGroupAssignment::class);

        $this->assertAssignmentCanBeDisabled();

        (new DisableUserGroupAssignment($assignment))();

        return new JsonResponse([]);
    }

    private function assertAssignmentCanBeDisabled(): void
    {
        WCF::getSession()->checkPermissions(['admin.management.canManageCronjob']);
    }
}
