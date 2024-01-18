<?php

namespace wcf\data\menu\item;

use wcf\data\DatabaseObject;
use wcf\data\ITitledObject;
use wcf\data\page\Page;
use wcf\data\page\PageCache;
use wcf\system\application\ApplicationHandler;
use wcf\system\exception\ImplementationException;
use wcf\system\page\handler\ILookupPageHandler;
use wcf\system\page\handler\IMenuPageHandler;
use wcf\system\WCF;

/**
 * Represents a menu item.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 *
 * @property-read   int $itemID         unique id of the menu item
 * @property-read   int $menuID         id of the menu the menu item belongs to
 * @property-read   int|null $parentItemID       id of the menu item's parent menu item or null if it has no parent menu item
 * @property-read   string $identifier     textual identifier of the menu item
 * @property-read   string $title          title of the menu item or name of language item which contains title
 * @property-read   int|null $pageID         id of the linked `wcf\data\page\Page` object or null of no such page is linked
 * @property-read   int $pageObjectID       id of the object required to show the page referenced by `$pageID`
 * @property-read   string $externalURL        external link of the menu item
 * @property-read   int $showOrder      position of the menu item in relation to its siblings
 * @property-read   int $isDisabled     is `1` if the menu item is disabled and thus not shown in the menu, otherwise `0`
 * @property-read   int $originIsSystem     is `1` if the menu item has been delivered by a package, otherwise `0` (if the menu item has been created by an admin in the ACP)
 * @property-read   int $packageID      id of the package the which delivers the menu item or `1` if it has been created in the ACP
 */
class MenuItem extends DatabaseObject implements ITitledObject
{
    /**
     * @var IMenuPageHandler
     */
    protected $handler;

    /**
     * page object
     * @var Page
     */
    protected $page;

    /**
     * @inheritDoc
     * @since   5.2
     */
    public function getTitle(): string
    {
        return WCF::getLanguage()->get($this->title);
    }

    /**
     * Returns true if the active user can delete this menu item.
     *
     * @return  bool
     */
    public function canDelete()
    {
        if (WCF::getSession()->getPermission('admin.content.cms.canManageMenu') && !$this->originIsSystem) {
            return true;
        }

        return false;
    }

    /**
     * Returns true if the active user can disable this menu item.
     *
     * @return  bool
     */
    public function canDisable()
    {
        if (WCF::getSession()->getPermission('admin.content.cms.canManageMenu')) {
            return true;
        }

        return false;
    }

    /**
     * Returns the URL of this menu item.
     *
     * @return  string
     */
    public function getURL()
    {
        if ($this->pageObjectID) {
            $handler = $this->getMenuPageHandler();
            if ($handler && $handler instanceof ILookupPageHandler) {
                return $handler->getLink($this->pageObjectID);
            }
        }

        if ($this->pageID) {
            return $this->getPage()->getLink();
        } else {
            return WCF::getLanguage()->get($this->externalURL);
        }
    }

    /**
     * Returns the page that is linked by this menu item.
     *
     * @return  Page|null
     */
    public function getPage()
    {
        if ($this->page === null && $this->pageID) {
            $this->page = PageCache::getInstance()->getPage($this->pageID);
        }

        return $this->page;
    }

    /**
     * Returns false if this item should be hidden from menu.
     *
     * @return  bool
     */
    public function isVisible()
    {
        if ($this->isDisabled) {
            return false;
        }

        if ($this->getPage() !== null && (!$this->getPage()->isVisible() || !$this->getPage()->isAccessible())) {
            return false;
        }

        if ($this->getMenuPageHandler() !== null) {
            $menuPageHandler = $this->getMenuPageHandler();
            if ($menuPageHandler instanceof ILookupPageHandler && !$menuPageHandler->isValid($this->pageObjectID ?: null)) {
                return false;
            }

            return $menuPageHandler->isVisible($this->pageObjectID ?: null);
        }

        return true;
    }

    /**
     * Returns the number of outstanding items for this menu.
     *
     * @return  int
     */
    public function getOutstandingItems()
    {
        if ($this->getMenuPageHandler() !== null) {
            return $this->getMenuPageHandler()->getOutstandingItemCount($this->pageObjectID ?: null);
        }

        return 0;
    }

    /**
     * Returns true if this item is an external link.
     *
     * @return bool
     */
    public function isExternalLink()
    {
        return $this->externalURL ? !ApplicationHandler::getInstance()->isInternalURL($this->externalURL) : false;
    }

    /**
     * Returns the page handler for this item.
     *
     * @return  IMenuPageHandler|null
     * @throws  ImplementationException
     */
    protected function getMenuPageHandler()
    {
        $page = $this->getPage();
        if ($page !== null && $page->handler) {
            if ($this->handler === null) {
                $className = $this->getPage()->handler;
                $this->handler = new $className();
                if (!($this->handler instanceof IMenuPageHandler)) {
                    throw new ImplementationException(\get_class($this->handler), IMenuPageHandler::class);
                }

                $this->handler->setMenuItem($this);
            }
        }

        return $this->handler;
    }

    public function cachePageObject(): void
    {
        if ($this->pageObjectID && $this->getMenuPageHandler() !== null) {
            $this->getMenuPageHandler()->cacheObject($this->pageObjectID);
        }
    }
}
