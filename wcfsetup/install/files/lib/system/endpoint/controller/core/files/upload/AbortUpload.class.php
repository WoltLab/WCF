<?php

namespace wcf\system\endpoint\controller\core\files\upload;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\file\temporary\FileTemporary;
use wcf\data\file\temporary\FileTemporaryBuilder;
use wcf\http\Helper;
use wcf\system\endpoint\DeleteRequest;
use wcf\system\endpoint\IController;

/**
 * Discards a pending upload including the chunks received so far.
 *
 * The identifier is the only proof of ownership, just like for `SaveChunk`:
 * whoever is able to add chunks to an upload is able to discard it.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
#[DeleteRequest('/core/files/upload/{identifier}')]
final class AbortUpload implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $fileTemporary = Helper::fetchObjectFromRequestParameter($variables['identifier'], FileTemporary::class);

        FileTemporaryBuilder::delete($fileTemporary);

        return new JsonResponse([]);
    }
}
