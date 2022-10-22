<?php

namespace wcf\system\database\table\column;

/**
 * Provides default implementation of the methods of `IAutoIncrementDatabaseTableColumn`.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Database\Table\Column
 * @since   5.2
 */
trait TAutoIncrementDatabaseTableColumn
{
    /**
     * is `true` if the values of the database table column are auto-increment
     */
    protected bool $autoIncrement = false;

    /**
     * Sets if the values of the database table column are auto-increment and returns this column.
     *
     * @return  $this
     */
    public function autoIncrement(bool $autoIncrement = true)
    {
        $this->autoIncrement = $autoIncrement;

        return $this;
    }

    /**
     * Returns `true` if the values of the database table column are auto-increment.
     */
    public function isAutoIncremented(): bool
    {
        return $this->autoIncrement;
    }
}
