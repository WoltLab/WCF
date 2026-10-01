<?php

namespace wcf\system\endpoint\controller\core\files;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\file\File;
use wcf\http\Helper;
use wcf\system\endpoint\DeleteRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\PermissionDeniedException;

/**
 * Deletes the file with the given ID.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
#[DeleteRequest('/core/files/{id}')]
final class DeleteFile implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $file = Helper::fetchObjectFromRequestParameter($variables['id'], File::class);

        $parameters = Helper::mapQueryParameters(
            $request->getQueryParams(),
            <<<'EOT'
                array {
                    uploaderToken?: non-empty-string
                }
                EOT,
        );

        $uploaderToken = $parameters['uploaderToken'] ?? null;
        if ($uploaderToken !== null) {
            $canDelete = $file->canDeleteWithUploaderToken($uploaderToken);
        } else {
            $canDelete = $file->canDelete();
        }

        if (!$canDelete) {
            throw new PermissionDeniedException();
        }

        new \wcf\command\file\DeleteFile($file)();

        return new JsonResponse([]);
    }
}
