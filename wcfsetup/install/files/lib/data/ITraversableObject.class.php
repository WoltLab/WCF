<?php

namespace wcf\data;

/**
 * Interface for enhanced iteration support.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @template TDatabaseObject of DatabaseObject|DatabaseObjectDecorator<DatabaseObject>
 * @extends \SeekableIterator<int, TDatabaseObject>
 */
interface ITraversableObject extends \SeekableIterator
{
    /**
     * Sets internal iterator pointer based upon related object id.
     *
     * @return void
     */
    public function seekTo(int $objectID);

    /**
     * Searches a specific object by object id and setting internal iterator
     * pointer to found item. Returns `null` if object id is not found.
     *
     * @return ?TDatabaseObject
     */
    public function search(int $objectID);
}
