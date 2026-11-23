<?php

namespace wcf\system\page\handler;

use wcf\data\menu\item\MenuItem;
use wcf\data\page\Page;

/**
 * Default implementation for pages supporting visibility and outstanding items.
 *
 * It is highly recommended to extend this class rather than implementing the interface
 * directly to achieve better upwards-compatibility in case of interface changes.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
abstract class AbstractMenuPageHandler implements IMenuPageHandler
{
    /**
     * @since 6.1
     */
    private MenuItem $menuItem;

    /**
     * @since 6.3
     */
    private Page $page;

    #[\Override]
    public function getOutstandingItemCount(?int $objectID = null)
    {
        return 0;
    }

    #[\Override]
    public function isVisible(?int $objectID = null)
    {
        return true;
    }

    #[\Override]
    public function cacheObject(int $objectID): void {}

    #[\Override]
    public function setMenuItem(MenuItem $menuItem): void
    {
        $this->menuItem = $menuItem;
    }

    #[\Override]
    public function getMenuItem(): ?MenuItem
    {
        return $this->menuItem ?? null;
    }

    /**
     * Sets the page this handler belongs to. This allows a handler that is
     * shared by multiple pages to identify the page it is used for, even when
     * it is not invoked from a menu item.
     *
     * @since 6.3
     */
    public function setPage(Page $page): void
    {
        $this->page = $page;
    }

    /**
     * @since 6.3
     */
    public function getPage(): ?Page
    {
        return $this->page ?? null;
    }
}
