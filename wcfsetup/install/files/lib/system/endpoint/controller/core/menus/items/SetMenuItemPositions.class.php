<?php

namespace wcf\system\endpoint\controller\core\menus\items;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\menu\Menu;
use wcf\data\menu\item\MenuItemList;
use wcf\http\Helper;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Sets the positions of the items of a menu.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
#[PostRequest('/core/menus/{id:\d+}/items/positions')]
final class SetMenuItemPositions implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $menu = Helper::fetchObjectFromRequestParameter($variables['id'], Menu::class);

        WCF::getSession()->checkPermissions(['admin.content.cms.canManageMenu']);

        $parameters = Helper::mapApiParameters($request, SetMenuItemPositionsParameters::class);
        $positions = $this->validatePositions($menu, $parameters->positions);

        (new \wcf\command\menu\item\SetMenuItemPositions($positions))();

        return new JsonResponse([]);
    }

    /**
     * @param array<int, list<int>> $positions
     * @return array<int, list<int>>
     */
    private function validatePositions(Menu $menu, array $positions): array
    {
        $menuItemIDs = [];
        foreach ($positions as $children) {
            $menuItemIDs = \array_merge($menuItemIDs, $children);
        }

        if ($menuItemIDs === []) {
            return $positions;
        }

        $menuItemList = new MenuItemList();
        $menuItemList->getConditionBuilder()->add('menu_item.itemID IN (?)', [$menuItemIDs]);
        $menuItemList->getConditionBuilder()->add('menu_item.menuID = ?', [$menu->menuID]);
        $menuItemList->readObjects();
        $menuItems = $menuItemList->getObjects();

        if (\count($menuItems) !== \count($menuItemIDs)) {
            throw new IllegalLinkException();
        }

        foreach ($positions as $parentItemID => $children) {
            if ($parentItemID && !isset($menuItems[$parentItemID])) {
                throw new IllegalLinkException();
            }
        }

        return $positions;
    }
}

/** @internal */
final class SetMenuItemPositionsParameters
{
    public function __construct(
        /** @var array<int, list<positive-int>> */
        public readonly array $positions,
    ) {}
}
