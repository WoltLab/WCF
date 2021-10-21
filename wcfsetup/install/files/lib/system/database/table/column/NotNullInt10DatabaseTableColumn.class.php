<?php

namespace wcf\system\database\table\column;

/**
 * Represents a `int` database table column with length `10` and whose values cannot be null.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Database\Table\Column
 * @since   5.2
 */
final class NotNullInt10DatabaseTableColumn
{
    /**
     * @inheritDoc
     */
    public static function create($name)
    {
        return IntDatabaseTableColumn::create($name)
            ->notNull()
            ->length(10);
    }

    private function __construct()
    {
    }
}
