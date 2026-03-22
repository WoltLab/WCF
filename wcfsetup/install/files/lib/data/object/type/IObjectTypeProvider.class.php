<?php

namespace wcf\data\object\type;

use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectDecorator;

/**
 * Any object type provider should implement this interface.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @template TDatabaseObject of DatabaseObject|DatabaseObjectDecorator
 */
interface IObjectTypeProvider
{
    /**
     * Returns an object by its ID.
     *
     * @return TDatabaseObject
     */
    public function getObjectByID(int $objectID);

    /**
     * Returns objects by their IDs.
     *
     * @param int[] $objectIDs
     * @return TDatabaseObject[]
     */
    public function getObjectsByIDs(array $objectIDs);
}
