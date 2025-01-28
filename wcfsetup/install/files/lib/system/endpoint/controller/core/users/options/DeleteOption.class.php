<?php

namespace wcf\system\endpoint\controller\core\users\options;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\user\option\UserOption;
use wcf\http\Helper;
use wcf\system\endpoint\DeleteRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * API endpoint for deleting user options.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
#[DeleteRequest('/core/users/options/{id:\d+}')]
final class DeleteOption implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $option = Helper::fetchObjectFromRequestParameter($variables['id'], UserOption::class);

        $this->assertOptionCanBeDeleted($option);

        (new \wcf\system\user\option\command\DeleteOption($option))();

        return new JsonResponse([]);
    }

    private function assertOptionCanBeDeleted(UserOption $option): void
    {
        WCF::getSession()->checkPermissions(['admin.user.canManageUserOption']);

        if (!$option->canDelete()) {
            throw new PermissionDeniedException();
        }
    }
}
