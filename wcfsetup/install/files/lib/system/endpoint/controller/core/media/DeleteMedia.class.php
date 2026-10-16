<?php

namespace wcf\system\endpoint\controller\core\media;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\command\media\DeleteMedia as DeleteMediaCommand;
use wcf\data\media\Media;
use wcf\http\Helper;
use wcf\system\endpoint\DeleteRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\PermissionDeniedException;

/**
 * Deletes the media file with the given ID.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
#[DeleteRequest('/core/media/{id:\d+}')]
final class DeleteMedia implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $media = Helper::fetchObjectFromRequestParameter($variables['id'], Media::class);

        if (!$media->canManage()) {
            throw new PermissionDeniedException();
        }

        new DeleteMediaCommand([$media])();

        return new JsonResponse([]);
    }
}
