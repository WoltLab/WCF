<?php

namespace wcf\system\database\table\column;

/**
 * Represents a `int` database table column with length `10`, whose values cannot be null, and whose
 * values are auto-incremented.
 *
 * This class should be used for the id column of DBO tables.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Database\Table\Column
 * @since   5.2
 */
final class ObjectIdDatabaseTableColumn
{
    /**
     * @inheritDoc
     */
    public static function create($name)
    {
        return NotNullInt10DatabaseTableColumn::create($name)
            ->autoIncrement();
    }

    private function __construct()
    {
    }
}
