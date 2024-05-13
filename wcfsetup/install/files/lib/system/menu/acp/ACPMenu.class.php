<?php

namespace wcf\system\menu\acp;

use wcf\event\acp\menu\item\ItemCollecting;
use wcf\system\cache\builder\ACPMenuCacheBuilder;
use wcf\system\event\EventHandler;
use wcf\system\menu\ITreeMenuItem;
use wcf\system\menu\TreeMenu;
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

    /**
     * @inheritDoc
     * @param AcpMenuItem $item
     */
    protected function checkMenuItem(ITreeMenuItem $item)
    {
        if (
            ENABLE_ENTERPRISE_MODE
            && !WCF::getUser()->hasOwnerAccess()
            && \in_array($item->menuItem, $this->enterpriseBlacklist)
        ) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    protected function loadCache()
    {
        parent::loadCache();

        if (PACKAGE_ID == 0) {
            return;
        }

        $this->loadLegacyMenuItems();

        $event = new ItemCollecting();
        EventHandler::getInstance()->fire($event);
        foreach ($event->getItems() as $item) {
            $this->menuItems[$item->parentMenuItem][] = $item;
        }
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

    protected function removeEmptyItems($parentMenuItem = '')
    {
        if (!isset($this->menuItems[$parentMenuItem])) {
            return;
        }

        foreach ($this->menuItems[$parentMenuItem] as $key => $item) {
            $this->removeEmptyItems($item->menuItem);
            if (
                !$item->getLink()
                && (!isset($this->menuItems[$item->menuItem]) || empty($this->menuItems[$item->menuItem]))
            ) {
                unset($this->menuItems[$parentMenuItem][$key]);
            }
        }
    }
}
