<?php

namespace wcf\system\endpoint\controller\core\users\groups\assignment;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\command\user\group\assignment\EnableUserGroupAssignment;
use wcf\data\user\group\assignment\UserGroupAssignment;
use wcf\http\Helper;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\WCF;

/**
 * Enables the user group assignments with the given ID.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
#[PostRequest("/core/users/groups/assignments/{id:\d+}/enable")]
final class EnableAssignment implements IController
{
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $assignment = Helper::fetchObjectFromRequestParameter($variables['id'], UserGroupAssignment::class);

        $this->assertAssignmentCanBeEnabled();

        if ($assignment->isDisabled) {
            (new EnableUserGroupAssignment($assignment))();
        }

        return new JsonResponse([]);
    }

    private function assertAssignmentCanBeEnabled(): void
    {
        WCF::getSession()->checkPermissions(['admin.management.canManageCronjob']);
    }
}
