<?php

namespace wcf\system\endpoint\controller\core\menus\items;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\menu\item\MenuItem;
use wcf\data\menu\item\MenuItemAction;
use wcf\http\Helper;
use wcf\system\endpoint\DeleteRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Deletes the menu item with the given ID.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
#[DeleteRequest("/core/menus/items/{id:\d+}")]
final class DeleteMenuItem implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $menuItem = Helper::fetchObjectFromRequestParameter($variables['id'], MenuItem::class);

        $this->assertMenuItemCanBeDeleted($menuItem);

        (new MenuItemAction([$menuItem], 'delete'))->executeAction();

        return new JsonResponse([]);
    }

    private function assertMenuItemCanBeDeleted(MenuItem $menuItem): void
    {
        WCF::getSession()->checkPermissions(['admin.content.cms.canManageMenu']);

        if (!$menuItem->canDelete()) {
            throw new PermissionDeniedException();
        }
    }
}
