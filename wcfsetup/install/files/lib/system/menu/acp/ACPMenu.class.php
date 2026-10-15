<?php

namespace wcf\system\menu\acp;

use wcf\event\acp\menu\item\ItemCollecting;
use wcf\system\cache\builder\ACPMenuCacheBuilder;
use wcf\system\event\EventHandler;
use wcf\system\menu\ITreeMenuItem;
use wcf\system\menu\TreeMenu;
use wcf\system\request\LinkHandler;
use wcf\system\request\RequestHandler;
use wcf\system\style\FontAwesomeIcon;
use wcf\system\WCF;

/**
 * Builds the acp menu.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class ACPMenu extends TreeMenu
{
    /**
     * list of acp menu items that are only visible for owners in enterprise mode
     * @var string[]
     * @since   5.2
     */
    protected $enterpriseBlacklist = [
        'wcf.acp.menu.link.language.multilingualism',
        'wcf.acp.menu.link.systemCheck',
    ];

    #[\Override]
    protected function checkMenuItem(ITreeMenuItem $item)
    {
        if (
            \ENABLE_ENTERPRISE_MODE !== 0
            && !WCF::getUser()->hasOwnerAccess()
            && \in_array($item->menuItem, $this->enterpriseBlacklist, true)
        ) {
            return false;
        }

        return true;
    }

    #[\Override]
    protected function loadCache()
    {
        parent::loadCache();

        if (\PACKAGE_ID === 0) {
            return;
        }

        $event = new ItemCollecting();
        EventHandler::getInstance()->fire($event);
        foreach ($event->getItems() as $item) {
            $this->menuItems[$item->parentMenuItem][] = $item;
        }

        $this->loadLegacyMenuItems();
    }

    /**
     * @deprecated 6.1
     */
    private function loadLegacyMenuItems(): void
    {
        $menuItems = ACPMenuCacheBuilder::getInstance()->getData();
        foreach ($menuItems as $parentMenuItem => $items) {
            foreach ($items as $item) {
                if (!parent::checkMenuItem($item)) {
                    continue;
                }

                $icon = null;
                if ($item->icon) {
                    if (FontAwesomeIcon::isValidString($item->icon)) {
                        $icon = FontAwesomeIcon::fromString($item->icon);
                    } elseif (\str_starts_with($item->icon, 'fa-')) {
                        // Safeguard to prevent legacy icons from breaking
                        // the admin panel during the upgrade to 6.0.
                        $icon = FontAwesomeIcon::fromString("question;true");
                    }
                }

                $this->menuItems[$parentMenuItem][] = new AcpMenuItem(
                    $item->menuItem,
                    $item->__toString(),
                    $item->parentMenuItem,
                    $item->getLink(),
                    $icon
                );
            }
        }
    }

    #[\Override]
    protected function removeEmptyItems(string $parentMenuItem = '')
    {
        if (!isset($this->menuItems[$parentMenuItem])) {
            return;
        }

        foreach ($this->menuItems[$parentMenuItem] as $key => $item) {
            $this->removeEmptyItems($item->menuItem);
            if (
                $item->getLink() === ''
                && (!isset($this->menuItems[$item->menuItem]) || empty($this->menuItems[$item->menuItem]))
            ) {
                unset($this->menuItems[$parentMenuItem][$key]);
            }
        }
    }

    /**
     * Returns the breadcrumb items for the active menu path, ordered from the
     * top-level section down to the immediate parent of the current page. The
     * active menu item is omitted if it represents the current page, but kept
     * otherwise, e.g. for edit forms that use the menu item of the list page.
     *
     * @return AcpMenuItem[]
     * @since 6.3
     */
    public function getBreadcrumbs(): array
    {
        if ($this->activeMenuItems === []) {
            return [];
        }

        $names = \array_reverse($this->activeMenuItems);
        $activeMenuItem = \array_pop($names);

        $breadcrumbs = [];
        foreach ($names as $name) {
            if (isset($this->menuItemList[$name])) {
                \assert($this->menuItemList[$name] instanceof AcpMenuItem);
                $breadcrumbs[] = $this->menuItemList[$name];
            }
        }

        if (isset($this->menuItemList[$activeMenuItem])) {
            \assert($this->menuItemList[$activeMenuItem] instanceof AcpMenuItem);
            if (!$this->isActiveRequest($this->menuItemList[$activeMenuItem])) {
                $breadcrumbs[] = $this->menuItemList[$activeMenuItem];
            }
        }

        return $breadcrumbs;
    }

    /**
     * Returns true if the given menu item links to the controller of the
     * active request. Menu items without a link are treated as the current
     * page.
     */
    private function isActiveRequest(AcpMenuItem $menuItem): bool
    {
        $link = $menuItem->getLink();
        if ($link === '') {
            return true;
        }

        $request = RequestHandler::getInstance()->getActiveRequest();
        if ($request === null) {
            return true;
        }

        // Controller links always end with a separator, which prevents false
        // positives for controllers sharing the same prefix.
        return \str_starts_with(
            $link,
            LinkHandler::getInstance()->getControllerLink($request->getClassName())
        );
    }
}
