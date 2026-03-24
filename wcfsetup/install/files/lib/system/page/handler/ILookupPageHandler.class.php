<?php

namespace wcf\system\page\handler;

/**
 * Extends the menu page handler interface by providing additional methods to lookup
 * pages identified by a unique object id.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
interface ILookupPageHandler extends IMenuPageHandler
{
    /**
     * Returns the link for a page with an object id.
     *
     * @return  string      page url
     */
    public function getLink(int $objectID);

    /**
     * Returns true if provided object id exists and is valid.
     *
     * @return  bool        true if object id is valid
     */
    public function isValid(int $objectID);

    /**
     * Performs a search for pages using a query string, returning an array containing
     * an `objectID => title` relation.
     *
     * @return list<array<string, mixed>>
     */
    public function lookup(string $searchString);
}
