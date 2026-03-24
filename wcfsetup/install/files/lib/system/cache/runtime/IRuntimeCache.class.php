<?php

namespace wcf\system\cache\runtime;

use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectDecorator;

/**
 * Handles runtime caches to centrally store objects fetched during runtime for reuse.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @template TDatabaseObject of DatabaseObject|DatabaseObjectDecorator
 */
interface IRuntimeCache
{
    /**
     * Caches the given object id so that during the next object fetch, the object with
     * this id will also be fetched.
     *
     * @return void
     */
    public function cacheObjectID(?int $objectID);

    /**
     * Caches the given object ids so that during the next object fetch, the objects with
     * these ids will also be fetched.
     *
     * @param int[] $objectIDs
     * @return void
     */
    public function cacheObjectIDs(array $objectIDs);

    /**
     * Returns all currently cached objects.
     *
     * @return array<int, ?TDatabaseObject>
     */
    public function getCachedObjects();

    /**
     * Returns the object with the given id or null if no such object exists.
     * If the given object id should not have been cached before, it will be cached
     * during this method call and the object, if existing, will be returned.
     *
     * @return ?TDatabaseObject
     */
    public function getObject(?int $objectID);

    /**
     * Returns the objects with the given ids. If an object does not exist, the array element
     * wil be null.
     * If the given object ids should not have been cached before, they will be cached
     * during this method call and the objects, if existing, will be returned.
     *
     * @param int[] $objectIDs
     * @return array<int, ?TDatabaseObject>
     */
    public function getObjects(array $objectIDs);

    /**
     * Removes the object with the given id from the runtime cache if it has already been loaded.
     *
     * @return void
     */
    public function removeObject(?int $objectID);

    /**
     * Removes the objects with the given ids from the runtime cache if they have already been loaded.
     *
     * @param int[] $objectIDs
     * @return void
     */
    public function removeObjects(array $objectIDs);
}
