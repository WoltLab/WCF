<?php

namespace wcf\system\page\handler;

use wcf\data\menu\item\MenuItem;

/**
 * Default implementation for pages supporting visibility and outstanding items.
 *
 * It is highly recommended to extend this class rather than implementing the interface
 * directly to achieve better upwards-compatibility in case of interface changes.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 */
abstract class AbstractMenuPageHandler implements IMenuPageHandler
{
    /**
     * @since 6.1
     */
    private MenuItem $menuItem;

    /**
     * @inheritDoc
     */
    public function getOutstandingItemCount($objectID = null)
    {
        return 0;
    }

    /**
     * @inheritDoc
     */
    public function isVisible($objectID = null)
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function cacheObject(int $objectID): void
    {
    }

    /**
     * @inheritDoc
     */
    public function setMenuItem(MenuItem $menuItem): void
    {
        $this->menuItem = $menuItem;
    }

    /**
     * @inheritDoc
     */
    public function getMenuItem(): MenuItem
    {
        return $this->menuItem;
    }
}
