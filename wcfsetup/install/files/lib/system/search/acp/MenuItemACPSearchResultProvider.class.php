<?php

namespace wcf\system\search\acp;

use wcf\system\menu\acp\ACPMenu;
use wcf\system\menu\acp\AcpMenuItem;
use wcf\system\WCF;

/**
 * ACP search provider implementation for menu items.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class MenuItemACPSearchResultProvider extends AbstractACPSearchResultProvider implements IACPSearchResultProvider
{
    /**
     * @inheritDoc
     */
    public function search($query)
    {
        $results = [];
        $menuItems = ACPMenu::getInstance()->menuItemList;

        foreach ($menuItems as $menuItem) {
            \assert($menuItem instanceof AcpMenuItem);

            if (\mb_stripos($menuItem->__toString(), $query) === false) {
                continue;
            }
            if (!$menuItem->getLink()) {
                continue;
            }

            $parentMenuItem = $menuItem->parentMenuItem;
            $parentMenuItems = [];
            while ($parentMenuItem && isset($menuItems[$parentMenuItem])) {
                \array_unshift($parentMenuItems, $parentMenuItem);

                $parentMenuItem = $menuItems[$parentMenuItem]->parentMenuItem;
            }
            $results[] = new ACPSearchResult(
                $menuItem->__toString(),
                $menuItem->getLink(),
                WCF::getLanguage()->getDynamicVariable(
                    'wcf.acp.search.result.subtitle',
                    ['pieces' => $parentMenuItems]
                )
            );
        }

        return $results;
    }
}
