<?php

namespace wcf\system\endpoint\controller\core\menus\items;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\menu\item\MenuItem;
use wcf\http\Helper;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Enables the menu item with the given ID.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
#[PostRequest("/core/menus/items/{id:\d+}/enable")]
final class EnableMenuItem implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $menuItem = Helper::fetchObjectFromRequestParameter($variables['id'], MenuItem::class);

        $this->assertMenuItemCanBeEnabled($menuItem);

        if ($menuItem->isDisabled) {
            (new \wcf\command\menu\item\EnableMenuItem($menuItem))();
        }

        return new JsonResponse([]);
    }

    private function assertMenuItemCanBeEnabled(MenuItem $menuItem): void
    {
        WCF::getSession()->checkPermissions(['admin.content.cms.canManageMenu']);

        if (!$menuItem->canDisable()) {
            throw new PermissionDeniedException();
        }
    }
}
