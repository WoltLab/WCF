<?php

namespace wcf\system\database\table\column;

/**
 * Provides default implementation of the methods of `IDefaultValueDatabaseTableColumn`.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Database\Table\Column
 * @since   5.5
 */
trait TDefaultValueDatabaseTableColumn
{
    /**
     * default value of the database table column
     * @var mixed
     */
    protected $defaultValue;

    /**
     * Checks if the given default value is valid.
     *
     * @param mixed $defaultValue validated default value
     * @throws  \InvalidArgumentException   if given default value is invalid
     */
    protected function validateDefaultValue($defaultValue)
    {
        // does nothing
    }

    /**
     * Sets the default value of the column and returns the column.
     *
     * @param mixed $defaultValue
     * @return  $this
     */
    public function defaultValue($defaultValue)
    {
        $this->validateDefaultValue($defaultValue);

        $this->defaultValue = $defaultValue;

        return $this;
    }

    /**
     * Returns the default value of the column.
     *
     * @return  mixed
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }
}
