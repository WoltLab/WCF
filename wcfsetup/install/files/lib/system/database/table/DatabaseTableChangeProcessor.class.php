<?php

namespace wcf\system\database\table;

use wcf\data\package\Package;
use wcf\system\database\editor\DatabaseEditor;
use wcf\system\database\table\column\AbstractIntDatabaseTableColumn;
use wcf\system\database\table\column\IDatabaseTableColumn;
use wcf\system\database\table\column\TinyintDatabaseTableColumn;
use wcf\system\database\table\index\DatabaseTableForeignKey;
use wcf\system\database\table\index\DatabaseTableIndex;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\package\SplitNodeException;
use wcf\system\WCF;

/**
 * Processes a given set of changes to database tables.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Database\Table
 * @since   5.2
 */
class DatabaseTableChangeProcessor
{
    /**
     * maps the registered database table column names to the ids of the packages they belong to
     * @var int[][]
     */
    protected $columnPackageIDs = [];

    /**
     * database table columns that will be added grouped by the name of the table to which they
     * will be added
     * @var IDatabaseTableColumn[][]
     */
    protected $columnsToAdd = [];

    /**
     * database table columns that will be altered grouped by the name of the table to which
     * they belong
     * @var IDatabaseTableColumn[][]
     */
    protected $columnsToAlter = [];

    /**
     * database table columns that will be dropped grouped by the name of the table from which
     * they will be dropped
     * @var IDatabaseTableColumn[][]
     */
    protected $columnsToDrop = [];

    /**
     * database editor to apply the relevant changes to the table layouts
     * @var DatabaseEditor
     */
    protected $dbEditor;

    /**
     * list of all existing tables in the used database
     * @var string[]
     */
    protected $existingTableNames = [];

    /**
     * existing database tables
     * @var DatabaseTable[]
     */
    protected $existingTables = [];

    /**
     * maps the registered database table index names to the ids of the packages they belong to
     * @var int[][]
     */
    protected $indexPackageIDs = [];

    /**
     * indices that will be added grouped by the name of the table to which they will be added
     * @var DatabaseTableIndex[][]
     */
    protected $indicesToAdd = [];

    /**
     * indices that will be dropped grouped by the name of the table from which they will be dropped
     * @var DatabaseTableIndex[][]
     */
    protected $indicesToDrop = [];

    /**
     * maps the registered database table foreign key names to the ids of the packages they belong to
     * @var int[][]
     */
    protected $foreignKeyPackageIDs = [];

    /**
     * foreign keys that will be added grouped by the name of the table to which they will be
     * added
     * @var DatabaseTableForeignKey[][]
     */
    protected $foreignKeysToAdd = [];

    /**
     * foreign keys that will be dropped grouped by the name of the table from which they will
     * be dropped
     * @var DatabaseTableForeignKey[][]
     */
    protected $foreignKeysToDrop = [];

    /**
     * package that wants to apply the changes
     * @var Package
     */
    protected $package;

    /**
     * message for the split node exception thrown after the changes have been applied
     * @var string
     */
    protected $splitNodeMessage = '';

    /**
     * layouts/layout changes of the relevant database table
     * @var DatabaseTable[]
     */
    protected $tables;

    /**
     * maps the registered database table names to the ids of the packages they belong to
     * @var int[]
     */
    protected $tablePackageIDs = [];

    /**
     * database table that will be created
     * @var DatabaseTable[]
     */
    protected $tablesToCreate = [];

    /**
     * database tables that will be dropped
     * @var DatabaseTable[]
     */
    protected $tablesToDrop = [];

    /**
     * Creates a new instance of `DatabaseTableChangeProcessor`.
     *
     * @param Package $package
     * @param DatabaseTable[] $tables
     * @param DatabaseEditor $dbEditor
     */
    public function __construct(Package $package, array $tables, DatabaseEditor $dbEditor)
    {
        $this->package = $package;

        $tableNames = [];
        foreach ($tables as $table) {
            if (!($table instanceof DatabaseTable)) {
                throw new \InvalidArgumentException("Tables must be instance of '" . DatabaseTable::class . "'");
            }

            $tableNames[] = $table->getName();
        }

        $this->tables = $tables;
        $this->dbEditor = $dbEditor;

        $this->existingTableNames = $dbEditor->getTableNames();

        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add('sqlTable IN (?)', [$tableNames]);
        $conditionBuilder->add('isDone = ?', [1]);

        $sql = "SELECT  *
                FROM    wcf" . WCF_N . "_package_installation_sql_log
                " . $conditionBuilder;
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute($conditionBuilder->getParameters());

        while ($row = $statement->fetchArray()) {
            if ($row['sqlIndex'] === '' && $row['sqlColumn'] === '') {
                $this->tablePackageIDs[$row['sqlTable']] = $row['packageID'];
            } elseif ($row['sqlIndex'] === '') {
                $this->columnPackageIDs[$row['sqlTable']][$row['sqlColumn']] = $row['packageID'];
            } elseif (\substr($row['sqlIndex'], -3) === '_fk') {
                $this->foreignKeyPackageIDs[$row['sqlTable']][$row['sqlIndex']] = $row['packageID'];
            } else {
                $this->indexPackageIDs[$row['sqlTable']][$row['sqlIndex']] = $row['packageID'];
            }
        }
    }

    /**
     * Adds the given index to the table.
     *
     * @param string $tableName
     * @param DatabaseTableForeignKey $foreignKey
     */
    protected function addForeignKey($tableName, DatabaseTableForeignKey $foreignKey)
    {
        $this->dbEditor->addForeignKey($tableName, $foreignKey->getName(), $foreignKey->getData());
    }

    /**
     * Adds the given index to the table.
     *
     * @param string $tableName
     * @param DatabaseTableIndex $index
     */
    protected function addIndex($tableName, DatabaseTableIndex $index)
    {
        $this->dbEditor->addIndex($tableName, $index->getName(), $index->getData());
    }

    /**
     * Applies all of the previously determined changes to achieve the desired database layout.
     *
     * @throws  SplitNodeException  if any change has been applied
     */
    protected function applyChanges()
    {
        $appliedAnyChange = false;

        foreach ($this->tablesToCreate as $table) {
            $appliedAnyChange = true;

            $this->prepareTableLog($table);
            $this->createTable($table);
            $this->finalizeTableLog($table);
        }

        foreach ($this->tablesToDrop as $table) {
            $appliedAnyChange = true;

            $this->dropTable($table);
            $this->deleteTableLog($table);
        }

        $columnTables = \array_unique(\array_merge(
            \array_keys($this->columnsToAdd),
            \array_keys($this->columnsToAlter),
            \array_keys($this->columnsToDrop)
        ));
        foreach ($columnTables as $tableName) {
            $appliedAnyChange = true;

            $columnsToAdd = $this->columnsToAdd[$tableName] ?? [];
            $columnsToAlter = $this->columnsToAlter[$tableName] ?? [];
            $columnsToDrop = $this->columnsToDrop[$tableName] ?? [];

            foreach ($columnsToAdd as $column) {
                $this->prepareColumnLog($tableName, $column);
            }

            $renamedColumnsWithLog = [];
            foreach ($columnsToAlter as $column) {
                if ($column->getNewName() && $this->getColumnLog($tableName, $column) !== null) {
                    $this->prepareColumnLog($tableName, $column, true);
                    $renamedColumnsWithLog[] = $column;
                }
            }

            $this->applyColumnChanges(
                $tableName,
                $columnsToAdd,
                $columnsToAlter,
                $columnsToDrop
            );

            foreach ($columnsToAdd as $column) {
                $this->finalizeColumnLog($tableName, $column);
            }

            foreach ($renamedColumnsWithLog as $column) {
                $this->finalizeColumnLog($tableName, $column, true);
                $this->deleteColumnLog($tableName, $column);
            }

            foreach ($columnsToDrop as $column) {
                $this->deleteColumnLog($tableName, $column);
            }
        }

        foreach ($this->foreignKeysToDrop as $tableName => $foreignKeys) {
            foreach ($foreignKeys as $foreignKey) {
                $appliedAnyChange = true;

                $this->dropForeignKey($tableName, $foreignKey);
                $this->deleteForeignKeyLog($tableName, $foreignKey);
            }
        }

        foreach ($this->foreignKeysToAdd as $tableName => $foreignKeys) {
            foreach ($foreignKeys as $foreignKey) {
                $appliedAnyChange = true;

                $this->prepareForeignKeyLog($tableName, $foreignKey);
                $this->addForeignKey($tableName, $foreignKey);
                $this->finalizeForeignKeyLog($tableName, $foreignKey);
            }
        }

        foreach ($this->indicesToDrop as $tableName => $indices) {
            foreach ($indices as $index) {
                $appliedAnyChange = true;

                $this->dropIndex($tableName, $index);
                $this->deleteIndexLog($tableName, $index);
            }
        }

        foreach ($this->indicesToAdd as $tableName => $indices) {
            foreach ($indices as $index) {
                $appliedAnyChange = true;

                $this->prepareIndexLog($tableName, $index);
                $this->addIndex($tableName, $index);
                $this->finalizeIndexLog($tableName, $index);
            }
        }

        if ($appliedAnyChange) {
            throw new SplitNodeException($this->splitNodeMessage);
        }
    }

    /**
     * Adds, alters, and drop columns of the same table.
     *
     * Before a column is dropped, all of its foreign keys are dropped.
     *
     * @param string $tableName
     * @param IDatabaseTableColumn[] $addedColumns
     * @param IDatabaseTableColumn[] $alteredColumns
     * @param IDatabaseTableColumn[] $droppedColumns
     */
    protected function applyColumnChanges($tableName, array $addedColumns, array $alteredColumns, array $droppedColumns)
    {
        $dropForeignKeys = [];

        $columnData = [];
        foreach ($droppedColumns as $droppedColumn) {
            $columnData[$droppedColumn->getName()] = [
                'action' => 'drop',
            ];

            foreach ($this->getExistingTable($tableName)->getForeignKeys() as $foreignKey) {
                if (\in_array($droppedColumn->getName(), $foreignKey->getColumns())) {
                    $dropForeignKeys[] = $foreignKey;
                }
            }
        }
        foreach ($addedColumns as $addedColumn) {
            $columnData[$addedColumn->getName()] = [
                'action' => 'add',
                'data' => $addedColumn->getData(),
            ];
        }
        foreach ($alteredColumns as $alteredColumn) {
            $columnData[$alteredColumn->getName()] = [
                'action' => 'alter',
                'data' => $alteredColumn->getData(),
                'newColumnName' => $alteredColumn->getNewName() ?? $alteredColumn->getName(),
            ];
        }

        if (!empty($columnData)) {
            foreach ($dropForeignKeys as $foreignKey) {
                $this->dropForeignKey($tableName, $foreignKey);
                $this->deleteForeignKeyLog($tableName, $foreignKey);
            }

            $this->dbEditor->alterColumns($tableName, $columnData);
        }
    }

    /**
     * Calculates all of the necessary changes to be executed.
     */
    protected function calculateChanges()
    {
        foreach ($this->tables as $table) {
            $tableName = $table->getName();

            if ($table->willBeDropped()) {
                if (\in_array($tableName, $this->existingTableNames)) {
                    $this->tablesToDrop[] = $table;

                    $this->splitNodeMessage .= "Dropped table '{$tableName}'.";
                    break;
                } elseif (isset($this->tablePackageIDs[$tableName])) {
                    $this->deleteTableLog($table);
                }
            } elseif (!\in_array($tableName, $this->existingTableNames)) {
                if ($table instanceof PartialDatabaseTable) {
                    throw new \LogicException("Partial table '{$tableName}' cannot be created.");
                }

                $this->tablesToCreate[] = $table;

                $this->splitNodeMessage .= "Created table '{$tableName}'.";
                break;
            } else {
                // calculate difference between tables
                $existingTable = $this->getExistingTable($tableName);
                $existingColumns = $existingTable->getColumns();

                foreach ($table->getColumns() as $column) {
                    if ($column->willBeDropped()) {
                        if (isset($existingColumns[$column->getName()])) {
                            if (!isset($this->columnsToDrop[$tableName])) {
                                $this->columnsToDrop[$tableName] = [];
                            }
                            $this->columnsToDrop[$tableName][] = $column;
                        } elseif (isset($this->columnPackageIDs[$tableName][$column->getName()])) {
                            $this->deleteColumnLog($tableName, $column);
                        }
                    } elseif (!isset($existingColumns[$column->getName()])) {
                        // It was already checked in `validate()` that for renames, the column either
                        // exists with the old or new name.
                        if (!$column->getNewName()) {
                            if (!isset($this->columnsToAdd[$tableName])) {
                                $this->columnsToAdd[$tableName] = [];
                            }
                            $this->columnsToAdd[$tableName][] = $column;
                        }
                    } elseif ($this->diffColumns($existingColumns[$column->getName()], $column)) {
                        if (!isset($this->columnsToAlter[$tableName])) {
                            $this->columnsToAlter[$tableName] = [];
                        }
                        $this->columnsToAlter[$tableName][] = $column;
                    }
                }

                // all column-related changes are executed in one query thus break
                // here and not within the previous loop
                if (!empty($this->columnsToAdd) || !empty($this->columnsToAlter) || !empty($this->columnsToDrop)) {
                    $this->splitNodeMessage .= "Altered columns of table '{$tableName}'.";
                    break;
                }

                $existingForeignKeys = $existingTable->getForeignKeys();
                foreach ($table->getForeignKeys() as $foreignKey) {
                    $matchingExistingForeignKey = null;
                    foreach ($existingForeignKeys as $existingForeignKey) {
                        if (empty(\array_diff($foreignKey->getDiffData(), $existingForeignKey->getDiffData()))) {
                            $matchingExistingForeignKey = $existingForeignKey;
                            break;
                        }
                    }

                    if ($foreignKey->willBeDropped()) {
                        if ($matchingExistingForeignKey !== null) {
                            if (!isset($this->foreignKeysToDrop[$tableName])) {
                                $this->foreignKeysToDrop[$tableName] = [];
                            }
                            $this->foreignKeysToDrop[$tableName][] = $foreignKey;

                            $this->splitNodeMessage .= "Dropped foreign key '{$tableName}." . \implode(
                                ',',
                                $foreignKey->getColumns()
                            ) . "'.";
                            break 2;
                        } elseif (isset($this->foreignKeyPackageIDs[$tableName][$foreignKey->getName()])) {
                            $this->deleteForeignKeyLog($tableName, $foreignKey);
                        }
                    } elseif ($matchingExistingForeignKey === null) {
                        // If the referenced database table does not already exists, delay the
                        // foreign key creation until after the referenced table has been created.
                        if (!\in_array($foreignKey->getReferencedTable(), $this->existingTableNames)) {
                            continue;
                        }

                        if (!isset($this->foreignKeysToAdd[$tableName])) {
                            $this->foreignKeysToAdd[$tableName] = [];
                        }
                        $this->foreignKeysToAdd[$tableName][] = $foreignKey;

                        $this->splitNodeMessage .= "Added foreign key '{$tableName}." . \implode(
                            ',',
                            $foreignKey->getColumns()
                        ) . "'.";
                        break 2;
                    } elseif (!empty(\array_diff($foreignKey->getData(), $matchingExistingForeignKey->getData()))) {
                        if (!isset($this->foreignKeysToDrop[$tableName])) {
                            $this->foreignKeysToDrop[$tableName] = [];
                        }
                        $this->foreignKeysToDrop[$tableName][] = $matchingExistingForeignKey;

                        if (!isset($this->foreignKeysToAdd[$tableName])) {
                            $this->foreignKeysToAdd[$tableName] = [];
                        }
                        $this->foreignKeysToAdd[$tableName][] = $foreignKey;

                        $this->splitNodeMessage .= "Replaced foreign key '{$tableName}." . \implode(
                            ',',
                            $foreignKey->getColumns()
                        ) . "'.";
                        break 2;
                    }
                }

                $existingIndices = $existingTable->getIndices();
                foreach ($table->getIndices() as $index) {
                    $matchingExistingIndex = null;
                    foreach ($existingIndices as $existingIndex) {
                        if (!$this->diffIndices($existingIndex, $index)) {
                            $matchingExistingIndex = $existingIndex;
                            break;
                        }
                    }

                    if ($index->willBeDropped()) {
                        if ($matchingExistingIndex !== null) {
                            if (!isset($this->indicesToDrop[$tableName])) {
                                $this->indicesToDrop[$tableName] = [];
                            }
                            $this->indicesToDrop[$tableName][] = $matchingExistingIndex;

                            $this->splitNodeMessage .= "Dropped index '{$tableName}." . \implode(
                                ',',
                                $index->getColumns()
                            ) . "'.";
                            break 2;
                        } elseif (isset($this->indexPackageIDs[$tableName][$index->getName()])) {
                            $this->deleteIndexLog($tableName, $index);
                        }
                    } elseif ($matchingExistingIndex !== null) {
                        // updating index type and index columns is supported with an
                        // explicit index name is given (automatically generated index
                        // names are not deterministic)
                        if (
                            !$index->hasGeneratedName()
                            && !empty(\array_diff($matchingExistingIndex->getData(), $index->getData()))
                        ) {
                            if (!isset($this->indicesToDrop[$tableName])) {
                                $this->indicesToDrop[$tableName] = [];
                            }
                            $this->indicesToDrop[$tableName][] = $matchingExistingIndex;

                            if (!isset($this->indicesToAdd[$tableName])) {
                                $this->indicesToAdd[$tableName] = [];
                            }
                            $this->indicesToAdd[$tableName][] = $index;
                        }
                    } else {
                        if (!isset($this->indicesToAdd[$tableName])) {
                            $this->indicesToAdd[$tableName] = [];
                        }
                        $this->indicesToAdd[$tableName][] = $index;

                        $this->splitNodeMessage .= "Added index '{$tableName}." . \implode(
                            ',',
                            $index->getColumns()
                        ) . "'.";
                        break 2;
                    }
                }
            }
        }
    }

    /**
     * Checks for any pending log entries for the package and either marks them as done or
     * deletes them so that after this method finishes, there are no more undone log entries
     * for the package.
     */
    protected function checkPendingLogEntries()
    {
        $sql = "SELECT  *
                FROM    wcf" . WCF_N . "_package_installation_sql_log
                WHERE   packageID = ?
                    AND isDone = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([$this->package->packageID, 0]);

        $doneEntries = $undoneEntries = [];
        while ($row = $statement->fetchArray()) {
            // table
            if ($row['sqlIndex'] === '' && $row['sqlColumn'] === '') {
                if (\in_array($row['sqlTable'], $this->existingTableNames)) {
                    $doneEntries[] = $row;
                } else {
                    $undoneEntries[] = $row;
                }
            } // column
            elseif ($row['sqlIndex'] === '') {
                if (isset($this->getExistingTable($row['sqlTable'])->getColumns()[$row['sqlColumn']])) {
                    $doneEntries[] = $row;
                } else {
                    $undoneEntries[] = $row;
                }
            } // foreign key
            elseif (\substr($row['sqlIndex'], -3) === '_fk') {
                if (isset($this->getExistingTable($row['sqlTable'])->getForeignKeys()[$row['sqlIndex']])) {
                    $doneEntries[] = $row;
                } else {
                    $undoneEntries[] = $row;
                }
            } // index
            else {
                if (isset($this->getExistingTable($row['sqlTable'])->getIndices()[$row['sqlIndex']])) {
                    $doneEntries[] = $row;
                } else {
                    $undoneEntries[] = $row;
                }
            }
        }

        WCF::getDB()->beginTransaction();
        foreach ($doneEntries as $entry) {
            $this->finalizeLog($entry);
        }

        // to achieve a consistent state, undone log entries will be deleted here even though
        // they might be re-created later to ensure that after this method finishes, there are
        // no more undone entries in the log for the relevant package
        foreach ($undoneEntries as $entry) {
            $this->deleteLog($entry);
        }
        WCF::getDB()->commitTransaction();
    }

    /**
     * Creates a done log entry for the given foreign key.
     *
     * @param string $tableName
     * @param DatabaseTableForeignKey $foreignKey
     */
    protected function createForeignKeyLog($tableName, DatabaseTableForeignKey $foreignKey)
    {
        $sql = "INSERT INTO wcf" . WCF_N . "_package_installation_sql_log
                            (packageID, sqlTable, sqlIndex, isDone)
                VALUES      (?, ?, ?, ?)";
        $statement = WCF::getDB()->prepareStatement($sql);

        $statement->execute([
            $this->package->packageID,
            $tableName,
            $foreignKey->getName(),
            1,
        ]);
    }

    /**
     * Creates the given table.
     *
     * @param DatabaseTable $table
     */
    protected function createTable(DatabaseTable $table)
    {
        $hasPrimaryKey = false;
        $columnData = \array_map(static function (IDatabaseTableColumn $column) use (&$hasPrimaryKey) {
            $data = $column->getData();
            if (isset($data['key']) && $data['key'] === 'PRIMARY') {
                $hasPrimaryKey = true;
            }

            return [
                'data' => $data,
                'name' => $column->getName(),
            ];
        }, $table->getColumns());
        $indexData = \array_map(static function (DatabaseTableIndex $index) {
            return [
                'data' => $index->getData(),
                'name' => $index->getName(),
            ];
        }, $table->getIndices());

        // Auto columns are implicitly defined as the primary key by MySQL.
        if ($hasPrimaryKey) {
            $indexData = \array_filter($indexData, static function ($key) {
                return $key !== 'PRIMARY';
            }, \ARRAY_FILTER_USE_KEY);
        }

        $this->dbEditor->createTable($table->getName(), $columnData, $indexData);

        foreach ($table->getForeignKeys() as $foreignKey) {
            // Only try to create the foreign key if the referenced database table already exists.
            // If it will be created later on, delay the foreign key creation until after the
            // referenced table has been created.
            if (
                \in_array($foreignKey->getReferencedTable(), $this->existingTableNames)
                || $foreignKey->getReferencedTable() === $table->getName()
            ) {
                $this->dbEditor->addForeignKey($table->getName(), $foreignKey->getName(), $foreignKey->getData());

                // foreign keys need to be explicitly logged for proper uninstallation
                $this->createForeignKeyLog($table->getName(), $foreignKey);
            }
        }
    }

    /**
     * Deletes the log entry for the given column.
     *
     * @param string $tableName
     * @param IDatabaseTableColumn $column
     */
    protected function deleteColumnLog($tableName, IDatabaseTableColumn $column)
    {
        $this->deleteLog(['sqlTable' => $tableName, 'sqlColumn' => $column->getName()]);
    }

    /**
     * Deletes the log entry for the given foreign key.
     *
     * @param string $tableName
     * @param DatabaseTableForeignKey $foreignKey
     */
    protected function deleteForeignKeyLog($tableName, DatabaseTableForeignKey $foreignKey)
    {
        $this->deleteLog(['sqlTable' => $tableName, 'sqlIndex' => $foreignKey->getName()]);
    }

    /**
     * Deletes the log entry for the given index.
     *
     * @param string $tableName
     * @param DatabaseTableIndex $index
     */
    protected function deleteIndexLog($tableName, DatabaseTableIndex $index)
    {
        $this->deleteLog(['sqlTable' => $tableName, 'sqlIndex' => $index->getName()]);
    }

    /**
     * Deletes a log entry.
     *
     * @param array $data
     */
    protected function deleteLog(array $data)
    {
        $sql = "DELETE FROM wcf" . WCF_N . "_package_installation_sql_log
                WHERE       packageID = ?
                        AND sqlTable = ?
                        AND sqlColumn = ?
                        AND sqlIndex = ?";
        $statement = WCF::getDB()->prepareStatement($sql);

        $statement->execute([
            $this->package->packageID,
            $data['sqlTable'],
            $data['sqlColumn'] ?? '',
            $data['sqlIndex'] ?? '',
        ]);
    }

    /**
     * Deletes all log entry related to the given table.
     *
     * @param DatabaseTable $table
     */
    protected function deleteTableLog(DatabaseTable $table)
    {
        $sql = "DELETE FROM wcf" . WCF_N . "_package_installation_sql_log
                WHERE       packageID = ?
                        AND sqlTable = ?";
        $statement = WCF::getDB()->prepareStatement($sql);

        $statement->execute([
            $this->package->packageID,
            $table->getName(),
        ]);
    }

    /**
     * Returns `true` if the two columns differ.
     *
     * @param IDatabaseTableColumn $oldColumn
     * @param IDatabaseTableColumn $newColumn
     * @return  bool
     */
    protected function diffColumns(IDatabaseTableColumn $oldColumn, IDatabaseTableColumn $newColumn)
    {
        $diff = \array_diff($oldColumn->getData(), $newColumn->getData());
        if (!empty($diff)) {
            // see https://github.com/WoltLab/WCF/pull/3167
            if (
                \array_key_exists('length', $diff)
                && $oldColumn instanceof AbstractIntDatabaseTableColumn
                && (
                    !($oldColumn instanceof TinyintDatabaseTableColumn)
                    || $oldColumn->getLength() != 1
                )
            ) {
                unset($diff['length']);
            }

            if (!empty($diff)) {
                return true;
            }
        }

        if ($newColumn->getNewName()) {
            return true;
        }

        // default type has to be checked explicitly for `null` to properly detect changing
        // from no default value (`null`) and to an empty string as default value (and vice
        // versa)
        if ($oldColumn->getDefaultValue() === null || $newColumn->getDefaultValue() === null) {
            return $oldColumn->getDefaultValue() !== $newColumn->getDefaultValue();
        }

        // for all other cases, use weak comparison so that `'1'` (from database) and `1`
        // (from script PIP) match, for example
        return $oldColumn->getDefaultValue() != $newColumn->getDefaultValue();
    }

    /**
     * Returns `true` if the two indices differ.
     *
     * @param DatabaseTableIndex $oldIndex
     * @param DatabaseTableIndex $newIndex
     * @return  bool
     */
    protected function diffIndices(DatabaseTableIndex $oldIndex, DatabaseTableIndex $newIndex)
    {
        if ($newIndex->hasGeneratedName()) {
            return !empty(\array_diff($oldIndex->getData(), $newIndex->getData()));
        }

        return $oldIndex->getName() !== $newIndex->getName();
    }

    /**
     * Drops the given foreign key.
     *
     * @param string $tableName
     * @param DatabaseTableForeignKey $foreignKey
     */
    protected function dropForeignKey($tableName, DatabaseTableForeignKey $foreignKey)
    {
        $this->dbEditor->dropForeignKey($tableName, $foreignKey->getName());
        $this->dbEditor->dropIndex($tableName, $foreignKey->getName());
    }

    /**
     * Drops the given index.
     *
     * @param string $tableName
     * @param DatabaseTableIndex $index
     */
    protected function dropIndex($tableName, DatabaseTableIndex $index)
    {
        $this->dbEditor->dropIndex($tableName, $index->getName());
    }

    /**
     * Drops the given table.
     *
     * @param DatabaseTable $table
     */
    protected function dropTable(DatabaseTable $table)
    {
        $this->dbEditor->dropTable($table->getName());
    }

    /**
     * Finalizes the log entry for the creation of the given column.
     *
     * @param string $tableName
     * @param IDatabaseTableColumn $column
     * @param bool $useNewName
     */
    protected function finalizeColumnLog($tableName, IDatabaseTableColumn $column, bool $useNewName = false)
    {
        $this->finalizeLog([
            'sqlTable' => $tableName,
            'sqlColumn' => $useNewName ? $column->getNewName() : $column->getName(),
        ]);
    }

    /**
     * Finalizes the log entry for adding the given index.
     *
     * @param string $tableName
     * @param DatabaseTableForeignKey $foreignKey
     */
    protected function finalizeForeignKeyLog($tableName, DatabaseTableForeignKey $foreignKey)
    {
        $this->finalizeLog(['sqlTable' => $tableName, 'sqlIndex' => $foreignKey->getName()]);
    }

    /**
     * Finalizes the log entry for adding the given index.
     *
     * @param string $tableName
     * @param DatabaseTableIndex $index
     */
    protected function finalizeIndexLog($tableName, DatabaseTableIndex $index)
    {
        $this->finalizeLog(['sqlTable' => $tableName, 'sqlIndex' => $index->getName()]);
    }

    /**
     * Finalizes a log entry after the relevant change has been executed.
     *
     * @param array $data
     */
    protected function finalizeLog(array $data)
    {
        $sql = "UPDATE  wcf" . WCF_N . "_package_installation_sql_log
                SET     isDone = ?
                WHERE   packageID = ?
                    AND sqlTable = ?
                    AND sqlColumn = ?
                    AND sqlIndex = ?";
        $statement = WCF::getDB()->prepareStatement($sql);

        $statement->execute([
            1,
            $this->package->packageID,
            $data['sqlTable'],
            $data['sqlColumn'] ?? '',
            $data['sqlIndex'] ?? '',
        ]);
    }

    /**
     * Finalizes the log entry for the creation of the given table.
     *
     * @param DatabaseTable $table
     */
    protected function finalizeTableLog(DatabaseTable $table)
    {
        $this->finalizeLog(['sqlTable' => $table->getName()]);
    }

    /**
     * Returns the log entry for the given column or `null` if there is no explicit entry for
     * this column.
     *
     * @param string $tableName
     * @param IDatabaseTableColumn $column
     * @return      array|null
     * @since       5.4
     */
    protected function getColumnLog(string $tableName, IDatabaseTableColumn $column): ?array
    {
        $sql = "SELECT  *
                FROM    wcf" . WCF_N . "_package_installation_sql_log
                WHERE   packageID = ?
                        AND sqlTable = ?
                        AND sqlColumn = ?";
        $statement = WCF::getDB()->prepareStatement($sql);

        $statement->execute([
            $this->package->packageID,
            $tableName,
            $column->getName(),
        ]);

        $row = $statement->fetchSingleRow();
        if ($row === false) {
            return null;
        }

        return $row;
    }

    /**
     * Returns the id of the package to with the given column belongs to. If there is no specific
     * log entry for the given column, the table log is checked and the relevant package id of
     * the whole table is returned. If the package of the table is also unknown, `null` is returned.
     *
     * @param DatabaseTable $table
     * @param IDatabaseTableColumn $column
     * @return  null|int
     */
    protected function getColumnPackageID(DatabaseTable $table, IDatabaseTableColumn $column)
    {
        if (isset($this->columnPackageIDs[$table->getName()][$column->getName()])) {
            return $this->columnPackageIDs[$table->getName()][$column->getName()];
        } elseif (isset($this->tablePackageIDs[$table->getName()])) {
            return $this->tablePackageIDs[$table->getName()];
        }

        return null;
    }

    /**
     * Returns the `DatabaseTable` object for the table with the given name.
     *
     * @param string $tableName
     * @return  DatabaseTable
     */
    protected function getExistingTable($tableName)
    {
        if (!isset($this->existingTables[$tableName])) {
            $this->existingTables[$tableName] = DatabaseTable::createFromExistingTable($this->dbEditor, $tableName);
        }

        return $this->existingTables[$tableName];
    }

    /**
     * Returns the id of the package to with the given foreign key belongs to. If there is no specific
     * log entry for the given foreign key, the table log is checked and the relevant package id of
     * the whole table is returned. If the package of the table is also unknown, `null` is returned.
     *
     * @param DatabaseTable $table
     * @param DatabaseTableForeignKey $foreignKey
     * @return  null|int
     */
    protected function getForeignKeyPackageID(DatabaseTable $table, DatabaseTableForeignKey $foreignKey)
    {
        if (isset($this->foreignKeyPackageIDs[$table->getName()][$foreignKey->getName()])) {
            return $this->foreignKeyPackageIDs[$table->getName()][$foreignKey->getName()];
        } elseif (isset($this->tablePackageIDs[$table->getName()])) {
            return $this->tablePackageIDs[$table->getName()];
        }

        return null;
    }

    /**
     * Returns the id of the package to with the given index belongs to. If there is no specific
     * log entry for the given index, the table log is checked and the relevant package id of
     * the whole table is returned. If the package of the table is also unknown, `null` is returned.
     *
     * @param DatabaseTable $table
     * @param DatabaseTableIndex $index
     * @return  null|int
     */
    protected function getIndexPackageID(DatabaseTable $table, DatabaseTableIndex $index)
    {
        if (isset($this->indexPackageIDs[$table->getName()][$index->getName()])) {
            return $this->indexPackageIDs[$table->getName()][$index->getName()];
        } elseif (isset($this->tablePackageIDs[$table->getName()])) {
            return $this->tablePackageIDs[$table->getName()];
        }

        return null;
    }

    /**
     * Prepares the log entry for the creation of the given column.
     *
     * @param string $tableName
     * @param IDatabaseTableColumn $column
     * @param bool $useNewName
     */
    protected function prepareColumnLog($tableName, IDatabaseTableColumn $column, bool $useNewName = false)
    {
        $this->prepareLog([
            'sqlTable' => $tableName,
            'sqlColumn' => $useNewName ? $column->getNewName() : $column->getName(),
        ]);
    }

    /**
     * Prepares the log entry for adding the given foreign key.
     *
     * @param string $tableName
     * @param DatabaseTableForeignKey $foreignKey
     */
    protected function prepareForeignKeyLog($tableName, DatabaseTableForeignKey $foreignKey)
    {
        $this->prepareLog(['sqlTable' => $tableName, 'sqlIndex' => $foreignKey->getName()]);
    }

    /**
     * Prepares the log entry for adding the given index.
     *
     * @param string $tableName
     * @param DatabaseTableIndex $index
     */
    protected function prepareIndexLog($tableName, DatabaseTableIndex $index)
    {
        $this->prepareLog(['sqlTable' => $tableName, 'sqlIndex' => $index->getName()]);
    }

    /**
     * Prepares a log entry before the relevant change has been executed.
     *
     * @param array $data
     */
    protected function prepareLog(array $data)
    {
        $sql = "INSERT INTO wcf" . WCF_N . "_package_installation_sql_log
                            (packageID, sqlTable, sqlColumn, sqlIndex, isDone)
                VALUES      (?, ?, ?, ?, ?)";
        $statement = WCF::getDB()->prepareStatement($sql);

        $statement->execute([
            $this->package->packageID,
            $data['sqlTable'],
            $data['sqlColumn'] ?? '',
            $data['sqlIndex'] ?? '',
            0,
        ]);
    }

    /**
     * Prepares the log entry for the creation of the given table.
     *
     * @param DatabaseTable $table
     */
    protected function prepareTableLog(DatabaseTable $table)
    {
        $this->prepareLog(['sqlTable' => $table->getName()]);
    }

    /**
     * Processes all tables and updates the current table layouts to match the specified layouts.
     *
     * @throws  \RuntimeException   if validation of the required layout changes fails
     */
    public function process()
    {
        $this->checkPendingLogEntries();

        $errors = $this->validate();
        if (!empty($errors)) {
            throw new \RuntimeException(WCF::getLanguage()->getDynamicVariable(
                'wcf.acp.package.error.databaseChange',
                [
                    'errors' => $errors,
                ]
            ));
        }

        $this->calculateChanges();

        $this->applyChanges();
    }

    /**
     * Checks if the relevant table layout changes can be executed and returns an array with information
     * on all validation errors.
     *
     * @return  array
     */
    public function validate()
    {
        $errors = [];
        foreach ($this->tables as $table) {
            if ($table->willBeDropped()) {
                if (\in_array($table->getName(), $this->existingTableNames)) {
                    if (!isset($this->tablePackageIDs[$table->getName()])) {
                        $errors[] = [
                            'tableName' => $table->getName(),
                            'type' => 'unregisteredTableDrop',
                        ];
                    } elseif ($this->tablePackageIDs[$table->getName()] !== $this->package->packageID) {
                        $errors[] = [
                            'tableName' => $table->getName(),
                            'type' => 'foreignTableDrop',
                        ];
                    }
                }
            } else {
                $existingTable = null;
                if (\in_array($table->getName(), $this->existingTableNames)) {
                    if (!isset($this->tablePackageIDs[$table->getName()])) {
                        $errors[] = [
                            'tableName' => $table->getName(),
                            'type' => 'unregisteredTableChange',
                        ];
                    } else {
                        $existingTable = DatabaseTable::createFromExistingTable($this->dbEditor, $table->getName());
                        $existingColumns = $existingTable->getColumns();
                        $existingIndices = $existingTable->getIndices();
                        $existingForeignKeys = $existingTable->getForeignKeys();

                        foreach ($table->getColumns() as $column) {
                            if (isset($existingColumns[$column->getName()])) {
                                $columnPackageID = $this->getColumnPackageID($table, $column);
                                if ($column->willBeDropped()) {
                                    if ($columnPackageID !== $this->package->packageID) {
                                        $errors[] = [
                                            'columnName' => $column->getName(),
                                            'tableName' => $table->getName(),
                                            'type' => 'foreignColumnDrop',
                                        ];
                                    }
                                } elseif ($columnPackageID !== $this->package->packageID) {
                                    $errors[] = [
                                        'columnName' => $column->getName(),
                                        'tableName' => $table->getName(),
                                        'type' => 'foreignColumnChange',
                                    ];
                                }
                            } elseif ($column->getNewName() && !isset($existingColumns[$column->getNewName()])) {
                                // Only show error message for a column rename if no column with the
                                // old or new name exists.
                                $errors[] = [
                                    'columnName' => $column->getName(),
                                    'tableName' => $table->getName(),
                                    'type' => 'renameNonexistingColumn',
                                ];
                            }
                        }

                        foreach ($table->getIndices() as $index) {
                            foreach ($existingIndices as $existingIndex) {
                                if (empty(\array_diff($index->getData(), $existingIndex->getData()))) {
                                    if ($index->willBeDropped()) {
                                        if ($this->getIndexPackageID($table, $index) !== $this->package->packageID) {
                                            $errors[] = [
                                                'columnNames' => \implode(',', $existingIndex->getColumns()),
                                                'tableName' => $table->getName(),
                                                'type' => 'foreignIndexDrop',
                                            ];
                                        }
                                    }

                                    continue 2;
                                }
                            }
                        }

                        foreach ($table->getForeignKeys() as $foreignKey) {
                            foreach ($existingForeignKeys as $existingForeignKey) {
                                if (empty(\array_diff($foreignKey->getData(), $existingForeignKey->getData()))) {
                                    if ($foreignKey->willBeDropped()) {
                                        if (
                                            $this->getForeignKeyPackageID(
                                                $table,
                                                $foreignKey
                                            ) !== $this->package->packageID
                                        ) {
                                            $errors[] = [
                                                'columnNames' => \implode(',', $existingForeignKey->getColumns()),
                                                'tableName' => $table->getName(),
                                                'type' => 'foreignForeignKeyDrop',
                                            ];
                                        }
                                    }

                                    continue 2;
                                }
                            }
                        }
                    }
                }

                foreach ($table->getIndices() as $index) {
                    foreach ($index->getColumns() as $indexColumn) {
                        $column = $this->getColumnByName($indexColumn, $table, $existingTable);
                        if ($column === null) {
                            if (!$index->willBeDropped()) {
                                $errors[] = [
                                    'columnName' => $indexColumn,
                                    'columnNames' => \implode(',', $index->getColumns()),
                                    'tableName' => $table->getName(),
                                    'type' => 'nonexistingColumnInIndex',
                                ];
                            }
                        } elseif (
                            $index->getType() === DatabaseTableIndex::PRIMARY_TYPE
                            && !$index->willBeDropped()
                            && !$column->isNotNull()
                        ) {
                            $errors[] = [
                                'columnName' => $indexColumn,
                                'columnNames' => \implode(',', $index->getColumns()),
                                'tableName' => $table->getName(),
                                'type' => 'nullColumnInPrimaryIndex',
                            ];
                        }
                    }
                }

                foreach ($table->getForeignKeys() as $foreignKey) {
                    $referencedTableExists = \in_array($foreignKey->getReferencedTable(), $this->existingTableNames);
                    foreach ($this->tables as $processedTable) {
                        if ($processedTable->getName() === $foreignKey->getReferencedTable()) {
                            $referencedTableExists = !$processedTable->willBeDropped();
                        }
                    }

                    if (!$referencedTableExists) {
                        $errors[] = [
                            'columnNames' => \implode(',', $foreignKey->getColumns()),
                            'referencedTableName' => $foreignKey->getReferencedTable(),
                            'tableName' => $table->getName(),
                            'type' => 'unknownTableInForeignKey',
                        ];
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Returns the column with the given name from the given table.
     *
     * @param string $columnName
     * @param DatabaseTable $updateTable
     * @param DatabaseTable|null $existingTable
     * @return      IDatabaseTableColumn|null
     * @since       5.2.10
     */
    protected function getColumnByName($columnName, DatabaseTable $updateTable, ?DatabaseTable $existingTable = null)
    {
        foreach ($updateTable->getColumns() as $column) {
            if (
                ($column->getNewName() === $columnName)
                || ($column->getName() === $columnName && !$column->getNewName())
            ) {
                return $column;
            }
        }

        if ($existingTable) {
            foreach ($existingTable->getColumns() as $column) {
                if ($column->getName() === $columnName) {
                    return $column;
                }
            }
        }

        return null;
    }
}
