<?php

namespace wcf\system\database\table\column;

/**
 * Every database table column that supports specifying a default value must implement this
 * interface.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Database\Table\Column
 * @since   5.5
 */
interface IDefaultValueDatabaseTableColumn extends IDatabaseTableColumn
{
    /**
     * Sets the default value of the column and returns the column.
     *
     * @param mixed $defaultValue
     * @return  $this
     */
    public function defaultValue($defaultValue);

    /**
     * Returns the default value of the column.
     *
     * @return  mixed
     */
    public function getDefaultValue();
}
