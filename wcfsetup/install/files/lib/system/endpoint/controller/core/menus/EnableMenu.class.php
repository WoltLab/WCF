<?php

namespace wcf\system\endpoint\controller\core\menus;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\menu\Menu;
use wcf\http\Helper;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Enables the box of the menu with the given ID.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
#[PostRequest('/core/menus/{id:\d+}/enable')]
final class EnableMenu implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $menu = Helper::fetchObjectFromRequestParameter($variables['id'], Menu::class);

        $this->assertMenuCanBeEnabled($menu);

        $box = $menu->getBox();
        if ($box->isDisabled) {
            new \wcf\command\box\EnableBox($box)();
        }

        return new JsonResponse([]);
    }

    private function assertMenuCanBeEnabled(Menu $menu): void
    {
        WCF::getSession()->checkPermissions(['admin.content.cms.canManageMenu']);

        if ($menu->isMainMenu()) {
            throw new PermissionDeniedException();
        }
    }
}
