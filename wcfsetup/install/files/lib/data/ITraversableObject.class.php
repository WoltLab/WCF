<?php

namespace wcf\data;

/**
 * Interface for enhanced iteration support.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Data
 */
interface ITraversableObject extends \SeekableIterator
{
    /**
     * Sets internal iterator pointer based upon related object id.
     *
     * @param   int     $objectID
     */
    public function seekTo($objectID);

    /**
     * Searches a specific object by object id and setting internal iterator
     * pointer to found item. Returns `null` if object id is not found.
     *
     * @param   int     $objectID
     * @return  DatabaseObject|null
     */
    public function search($objectID);
}
