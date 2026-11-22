<?php

namespace wcf\system\endpoint\controller\core\pageNotices;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\command\pageNotice\DeletePageNotices;
use wcf\data\page\notice\PageNotice;
use wcf\http\Helper;
use wcf\system\endpoint\DeleteRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\PermissionDeniedException;

/**
 * Deletes the page notice with the given ID.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
#[DeleteRequest('/core/page-notices/{id:\d+}')]
final class DeletePageNotice implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $pageNotice = Helper::fetchObjectFromRequestParameter($variables['id'], PageNotice::class);
        if (!$pageNotice->canEdit()) {
            throw new PermissionDeniedException();
        }

        new DeletePageNotices([$pageNotice])();

        return new JsonResponse([]);
    }
}
