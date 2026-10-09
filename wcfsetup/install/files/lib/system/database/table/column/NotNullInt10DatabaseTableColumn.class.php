<?php

namespace wcf\system\database\table\column;

/**
 * Represents a `int` database table column whose values cannot be null.
 *
 * The `10` in the name is a leftover of the display width that was previously set. It has no
 * effect on the range of values and is no longer set.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
final class NotNullInt10DatabaseTableColumn
{
    public static function create(string $name): IntDatabaseTableColumn
    {
        return IntDatabaseTableColumn::create($name)
            ->notNull();
    }

    private function __construct()
    {
    }
}
