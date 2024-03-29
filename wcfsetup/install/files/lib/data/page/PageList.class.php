<?php

namespace wcf\data\page;

use wcf\data\DatabaseObjectList;

/**
 * Represents a list of pages.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 *
 * @method  Page        current()
 * @method  Page[]      getObjects()
 * @method  Page|null   getSingleObject()
 * @method  Page|null   search($objectID)
 * @property    Page[] $objects
 */
class PageList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $className = Page::class;
}
