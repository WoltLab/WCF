<?php

namespace wcf\system\database\table\column;

/**
 * Every database table column whose values can be auto-incremented must implement this interface.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
interface IAutoIncrementDatabaseTableColumn
{
    /**
     * Sets if the values of the database table column are auto-increment and returns this column.
     *
     * @return  $this
     */
    public function autoIncrement(bool $autoIncrement = true): static;

    /**
     * Returns `true` if the values of the database table column are auto-increment.
     */
    public function isAutoIncremented(): bool;
}
