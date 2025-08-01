<?php

namespace wcf\system\endpoint\controller\core\menus;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\menu\Menu;
use wcf\data\menu\MenuAction;
use wcf\http\Helper;
use wcf\system\endpoint\DeleteRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Deletes the menu with the given ID.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
#[DeleteRequest("/core/menus/{id:\d+}")]
final class DeleteMenu implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $menu = Helper::fetchObjectFromRequestParameter($variables['id'], Menu::class);

        $this->assertMenuCanBeDeleted($menu);

        (new MenuAction([$menu], 'delete'))->executeAction();

        return new JsonResponse([]);
    }

    private function assertMenuCanBeDeleted(Menu $menu): void
    {
        WCF::getSession()->checkPermissions(['admin.content.cms.canManageMenu']);

        if (!$menu->canDelete()) {
            throw new PermissionDeniedException();
        }
    }
}
