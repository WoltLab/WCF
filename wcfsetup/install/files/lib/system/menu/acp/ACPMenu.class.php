<?php

namespace wcf\system\menu\acp;

use wcf\system\cache\builder\ACPMenuCacheBuilder;
use wcf\system\event\EventHandler;
use wcf\system\menu\acp\event\ACPMenuCollecting;
use wcf\system\menu\ITreeMenuItem;
use wcf\system\menu\TreeMenu;
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
     * @param ACPMenuItem $item
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

        $event = new ACPMenuCollecting();
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

                $this->menuItems[$parentMenuItem][] = new ACPMenuItem(
                    $item->menuItem,
                    $item->__toString(),
                    $item->parentMenuItem,
                    $item->getLink(),
                    $item->icon ?? ''
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
