<?php

namespace wcf\system\database\table\column;

/**
 * Represents a column of a database table.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Database\Table\Column
 * @since   5.2
 */
interface IDatabaseTableColumn
{
    /**
     * Marks the column to be dropped and returns the column.
     *
     * @return  $this
     */
    public function drop();

    /**
     * Returns the data used by `DatabaseEditor` to add the column to a table.
     *
     * @return  array
     */
    public function getData();

    /**
     * Returns the name of the column.
     *
     * @return  string
     */
    public function getName();

    /**
     * Returns the new name of the column or `null` if the column's name is unchanged.
     *
     * @since       5.4
     */
    public function getNewName(): ?string;

    /**
     * Returns the type of the column.
     *
     * @return  string
     */
    public function getType();

    /**
     * Returns `true` if the values of the column cannot be `null`.
     *
     * @return  bool
     */
    public function isNotNull();

    /**
     * Sets the name of the column and returns the column.
     *
     * @param string $name
     * @return  $this
     */
    public function name($name);

    /**
     * Sets if the values of the column cannot be `null`.
     *
     * @param bool $notNull
     * @return  $this
     */
    public function notNull($notNull = true);

    /**
     * Sets the new name of the column and returns the column.
     *
     * @since       5.4
     * @return $this
     */
    public function renameTo(string $newName);

    /**
     * Returns `true` if the column will be dropped.
     *
     * @return  bool
     */
    public function willBeDropped();

    /**
     * Returns a `DatabaseTableColumn` object with the given name.
     *
     * @param string $name
     * @return  $this
     */
    public static function create($name);

    /**
     * Returns a `DatabaseTableColumn` object with the given name and data.
     *
     * @param string $name
     * @param array $data data returned by `DatabaseEditor::getColumns()`
     * @return  $this
     */
    public static function createFromData($name, array $data);
}
