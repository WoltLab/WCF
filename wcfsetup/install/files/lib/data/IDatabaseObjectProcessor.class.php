<?php

namespace wcf\data;

/**
 * Default interface for DatabaseObject processors.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
interface IDatabaseObjectProcessor
{
    public function __construct(DatabaseObject $object);

    /**
     * Delegates accesses to inaccessible object properties the processed object.
     *
     * @return mixed
     */
    public function __get(string $name);

    /**
     * Delegates isset calls for inaccessible object properties to the processed
     * object.
     *
     * @return bool
     */
    public function __isset(string $name);

    /**
     * Delegates inaccessible method calls to the processed database object.
     *
     * @param mixed[] $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments);
}
