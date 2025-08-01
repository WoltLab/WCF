<?php

namespace wcf\system\endpoint\controller\core\cronjobs;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\cronjob\Cronjob;
use wcf\data\cronjob\CronjobAction;
use wcf\http\Helper;
use wcf\system\endpoint\DeleteRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Deletes the cronjob with the given ID.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
#[DeleteRequest('/core/cronjobs/{id:\d+}')]
final class DeleteCronjob implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $cronjob = Helper::fetchObjectFromRequestParameter($variables['id'], Cronjob::class);

        $this->assertCronjobCanBeDeleted($cronjob);

        (new CronjobAction([$cronjob], 'delete'))->executeAction();

        return new JsonResponse([]);
    }

    private function assertCronjobCanBeDeleted(Cronjob $cronjob): void
    {
        WCF::getSession()->checkPermissions(['admin.management.canManageCronjob']);

        if (!$cronjob->isDeletable()) {
            throw new PermissionDeniedException();
        }
    }
}
