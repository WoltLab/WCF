<?php

namespace wcf\system\page\handler;

use wcf\data\menu\item\MenuItem;

/**
 * Default interface for pages supporting visibility and outstanding items.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 */
interface IMenuPageHandler
{
    /**
     * Returns the number of outstanding items for this page for display as a badge, optionally
     * specifying a corresponding object id to limit the scope.
     *
     * @param int|null $objectID optional page object id
     * @return  int     number of outstanding items
     */
    public function getOutstandingItemCount($objectID = null);

    /**
     * Returns false if this page should be hidden from menus, but does not control the accessibility
     * of the page itself. The visibility can optionally be scoped to the given object id.
     *
     * @param int|null $objectID optional page object id
     * @return  bool        false if the page should be hidden from menus
     */
    public function isVisible($objectID = null);

    /**
     * Caches the given object id to save SQL queries if multiple objects of the same type are queried in the menu.
     *
     * @since 6.0
     */
    public function cacheObject(int $objectID): void;

    /**
     * Sets the menu item of this handler.
     * This is intended to allow the handler to react to information from the MenuItem.
     *
     * @since 6.1
     */
    public function setMenuItem(MenuItem $menuItem): void;

    /**
     * @since 6.1
     */
    public function getMenuItem(): ?MenuItem;
}
