<?php

namespace wcf\system\database\table\column;

/**
 * Represents a `int` database table column whose values cannot be null and are auto-incremented.
 *
 * This class should be used for the id column of DBO tables.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
final class ObjectIdDatabaseTableColumn
{
    public static function create(string $name): IDatabaseTableColumn
    {
        return IntDatabaseTableColumn::create($name)
            ->notNull()
            ->autoIncrement();
    }

    private function __construct()
    {
    }
}
