<?php

namespace wcf\system\endpoint\controller\core\pages;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\page\Page;
use wcf\data\page\PageAction;
use wcf\http\Helper;
use wcf\system\endpoint\DeleteRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Deletes the page with the given ID.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
#[DeleteRequest('/core/pages/{id:\d+}')]
final class DeletePage implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $page = Helper::fetchObjectFromRequestParameter($variables['id'], Page::class);

        $this->assertPageCanBeDeleted($page);

        (new PageAction([$page], 'delete'))->executeAction();

        return new JsonResponse([]);
    }

    private function assertPageCanBeDeleted(Page $page): void
    {
        WCF::getSession()->checkPermissions(['admin.content.cms.canManagePage']);

        if (!$page->canDelete()) {
            throw new PermissionDeniedException();
        }
    }
}
