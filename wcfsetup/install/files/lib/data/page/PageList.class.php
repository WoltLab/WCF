<?php

namespace wcf\data\page;

use wcf\data\DatabaseObjectList;

/**
 * Represents a list of pages.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends DatabaseObjectList<Page>
 */
class PageList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $className = Page::class;
}
