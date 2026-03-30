<?php

namespace wcf\data;

/**
 * Abstract class for all data holder classes.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @template T of IStorableObject
 */
interface IEditableObject extends IStorableObject
{
    /**
     * Creates a new object.
     *
     * @param array<string, mixed> $parameters
     * @return T
     */
    public static function create(array $parameters = []);

    /**
     * Updates this object.
     *
     * @param array<string, mixed> $parameters
     * @return void
     */
    public function update(array $parameters = []);

    /**
     * Updates the counters of this object.
     *
     * @param array<string, int|float> $counters
     * @return void
     */
    public function updateCounters(array $counters = []);

    /**
     * Deletes this object.
     *
     * @return void
     */
    public function delete();

    /**
     * Deletes all objects with the given ids and returns the number of deleted
     * objects.
     *
     * @param (string|int)[] $objectIDs
     * @return int
     */
    public static function deleteAll(array $objectIDs = []);
}
