<?php

namespace wcf\system\database\editor;

use wcf\system\database\Database;
use wcf\system\exception\NotImplementedException;

/**
 * Abstract implementation of a database editor.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @phpstan-type ColumnDefinition array{
 *  autoIncrement?: bool|0|1,
 *  decimals?: int,
 *  default?: string|int|float,
 *  key?: string|false,
 *  length?: ?int,
 *  notNull?: bool|0|1,
 *  type: string,
 *  values?: string,
 * }
 * @phpstan-type ForeignKeyDefinition array{
 *  action?: string,
 *  operation?: string,
 *  columns: string,
 *  referencedTable: string,
 *  referencedColumns: string,
 *  'ON DELETE'?: string,
 *  'ON UPDATE'?: string,
 * }
 * @phpstan-type IndexDefinition array{type: string, columns: string}
 * @phpstan-type ExistingColumnDefinition array{
 *  type: string,
 *  length?: ?int,
 *  notNull: bool,
 *  key: string,
 *  default: string|int|float|null,
 *  autoIncrement: bool,
 *  enumValues: string,
 *  decimals: int,
 * }
 */
abstract class DatabaseEditor
{
    /**
     * database object
     * @var Database
     */
    protected $dbObj;

    /**
     * @param Database $dbObj
     */
    public function __construct(Database $dbObj)
    {
        $this->dbObj = $dbObj;
    }

    /**
     * Returns all existing table names.
     *
     * @return string[] $existingTables
     */
    abstract public function getTableNames();

    /**
     * Returns the columns of a table.
     *
     * @return mixed[]
     */
    abstract public function getColumns(string $tableName);

    /**
     * Returns information on the foreign keys of a table.
     *
     * @return array<string, array{columns: string[], referencedColumns: string[], referencedTable?: string, 'ON DELETE'?: string, 'ON UPDATE'?: string}>
     */
    public function getForeignKeys(string $tableName)
    {
        throw new NotImplementedException();
    }

    /**
     * Returns the names of the indices of a table.
     *
     * @return  string[]    $indices
     */
    abstract public function getIndices(string $tableName);

    /**
     * Returns information on the indices of a table.
     *
     * @return array<string, array{columns: string[], type: string}>
     */
    public function getIndexInformation(string $tableName)
    {
        throw new NotImplementedException();
    }

    /**
     * Creates a new database table.
     *
     * @param array<array{name: string, data: ColumnDefinition}> $columns
     * @param array<array{name: string, data: IndexDefinition|ForeignKeyDefinition}> $indices
     * @return void
     */
    abstract public function createTable(string $tableName, array $columns, array $indices = []);

    /**
     * Drops a database table.
     *
     * @return void
     */
    abstract public function dropTable(string $tableName);

    /**
     * Adds a new column to an existing database table.
     *
     * @param ColumnDefinition $columnData
     * @return void
     */
    abstract public function addColumn(string $tableName, string $columnName, array $columnData);

    /**
     * Alters an existing column.
     *
     * @param ColumnDefinition $newColumnData
     * @return void
     */
    abstract public function alterColumn(string $tableName, string $oldColumnName, string $newColumnName, array $newColumnData);

    /**
     * Adds, alters and drops multiple columns at once.
     *
     * @param array<string|int, mixed[]> $alterData
     * @return void
     */
    public function alterColumns(string $tableName, array $alterData)
    {
        throw new NotImplementedException();
    }

    /**
     * Drops an existing column.
     *
     * @return void
     */
    abstract public function dropColumn(string $tableName, string $columnName);

    /**
     * Adds a new index to an existing database table.
     *
     * @param IndexDefinition $indexData
     * @return void
     */
    abstract public function addIndex(string $tableName, string $indexName, array $indexData);

    /**
     * Adds a new foreign key to an existing database table.
     *
     * @param ForeignKeyDefinition $indexData
     * @return void
     */
    abstract public function addForeignKey(string $tableName, string $indexName, array $indexData);

    /**
     * Drops an existing index.
     *
     * @return void
     */
    abstract public function dropIndex(string $tableName, string $indexName);

    /**
     * Drops existing primary keys.
     *
     * @return void
     */
    abstract public function dropPrimaryKey(string $tableName);

    /**
     * Drops an existing foreign key.
     *
     * @return void
     */
    abstract public function dropForeignKey(string $tableName, string $indexName);
}
