<?php

namespace wcf\system\endpoint\controller\core\boxes;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\box\Box;
use wcf\data\box\BoxAction;
use wcf\http\Helper;
use wcf\system\endpoint\DeleteRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\PermissionDeniedException;

/**
 * Enables the media provider with the given ID.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
#[DeleteRequest('/core/boxes/{id:\d+}')]
final class DeleteBox implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $box = Helper::fetchObjectFromRequestParameter($variables['id'], Box::class);

        $this->assertBoxCanBeDeleted($box);

        (new BoxAction([$box], 'delete'))->executeAction();

        return new JsonResponse([]);
    }

    private function assertBoxCanBeDeleted(Box $box): void
    {
        if (!$box->canDelete()) {
            throw new PermissionDeniedException();
        }
    }
}
