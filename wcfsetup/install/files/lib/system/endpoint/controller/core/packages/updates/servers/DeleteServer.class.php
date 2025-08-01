<?php

namespace wcf\system\endpoint\controller\core\packages\updates\servers;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\package\update\server\PackageUpdateServer;
use wcf\data\package\update\server\PackageUpdateServerAction;
use wcf\http\Helper;
use wcf\system\endpoint\DeleteRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Deletes the package update server with the given ID.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
#[DeleteRequest('/core/packages/updates/servers/{id}')]
final class DeleteServer implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $server = Helper::fetchObjectFromRequestParameter($variables['id'], PackageUpdateServer::class);

        $this->assertServerCanBeDeleted($server);

        (new PackageUpdateServerAction([$server], 'delete'))->executeAction();

        return new JsonResponse([]);
    }

    private function assertServerCanBeDeleted(PackageUpdateServer $server): void
    {
        WCF::getSession()->checkPermissions(['admin.configuration.package.canEditServer']);

        if (!$server->canDelete()) {
            throw new PermissionDeniedException();
        }
    }
}
