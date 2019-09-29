<?php
namespace wcf\system\database\table;
use wcf\data\package\Package;
use wcf\system\database\editor\DatabaseEditor;
use wcf\system\database\table\column\IDatabaseTableColumn;
use wcf\system\database\table\index\DatabaseTableForeignKey;
use wcf\system\database\table\index\DatabaseTableIndex;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\package\SplitNodeException;
use wcf\system\WCF;

/**
 * Processes a given set of changes to database tables.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Database\Table
 * @since	5.2
 */
class DatabaseTableChangeProcessor {
	/**
	 * maps the registered database table column names to the ids of the packages they belong to
	 * @var	int[][]
	 */
	protected $columnPackageIDs = [];
	
	/**
	 * database table columns that will be added grouped by the name of the table to which they
	 * will be added
	 * @var	IDatabaseTableColumn[][]
	 */
	protected $columnsToAdd = [];
	
	/**
	 * database table columns that will be altered grouped by the name of the table to which
	 * they belong
	 * @var	IDatabaseTableColumn[][]
	 */
	protected $columnsToAlter = [];
	
	/**
	 * database table columns that will be dropped grouped by the name of the table from which
	 * they will be dropped
	 * @var	IDatabaseTableColumn[][]
	 */
	protected $columnsToDrop = [];
	
	/**
	 * database editor to apply the relevant changes to the table layouts
	 * @var	DatabaseEditor
	 */
	protected $dbEditor;

	/**
	 * list of all existing tables in the used database
	 * @var	string[]
	 */
	protected $existingTableNames = [];
	
	/**
	 * existing database tables
	 * @var	DatabaseTable[]
	 */
	protected $existingTables = [];
	
	/**
	 * maps the registered database table index names to the ids of the packages they belong to
	 * @var	int[][]
	 */
	protected $indexPackageIDs = [];
	
	/**
	 * indices that will be added grouped by the name of the table to which they will be added
	 * @var	DatabaseTableIndex[][] 
	 */
	protected $indicesToAdd = [];
	
	/**
	 * indices that will be dropped grouped by the name of the table from which they will be dropped
	 * @var	DatabaseTableIndex[][]
	 */
	protected $indicesToDrop = [];
	
	/**
	 * maps the registered database table foreign key names to the ids of the packages they belong to
	 * @var	int[][]
	 */
	protected $foreignKeyPackageIDs = [];
	
	/**
	 * foreign keys that will be added grouped by the name of the table to which they will be
	 * added
	 * @var	DatabaseTableForeignKey[][]
	 */
	protected $foreignKeysToAdd = [];
	
	/**
	 * foreign keys that will be dropped grouped by the name of the table from which they will
	 * be dropped
	 * @var	DatabaseTableForeignKey[][]
	 */
	protected $foreignKeysToDrop = [];
	
	/**
	 * package that wants to apply the changes
	 * @var	Package
	 */
	protected $package;
	
	/**
	 * message for the split node exception thrown after the changes have been applied
	 * @var	string
	 */
	protected $splitNodeMessage = '';
	
	/**
	 * layouts/layout changes of the relevant database table
	 * @var	DatabaseTable[]
	 */
	protected $tables;
	
	/**
	 * maps the registered database table names to the ids of the packages they belong to
	 * @var	int[]
	 */
	protected $tablePackageIDs = [];
	
	/**
	 * database table that will be created
	 * @var	DatabaseTable[]
	 */
	protected $tablesToCreate = [];
	
	/**
	 * database tables that will be dropped
	 * @var	DatabaseTable[]
	 */
	protected $tablesToDrop = [];
	
	/**
	 * Creates a new instance of `DatabaseTableChangeProcessor`.
	 * 
	 * @param	Package			$package
	 * @param	DatabaseTable[]		$tables
	 * @param	DatabaseEditor		$dbEditor
	 */
	public function __construct(Package $package, array $tables, DatabaseEditor $dbEditor) {
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
		
		$sql = "SELECT	*
			FROM	wcf" . WCF_N . "_package_installation_sql_log
			" . $conditionBuilder;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditionBuilder->getParameters());
		
		while ($row = $statement->fetchArray()) {
			if ($row['sqlIndex'] === '' && $row['sqlColumn'] === '') {
				$this->tablePackageIDs[$row['sqlTable']] = $row['packageID'];
			}
			else if ($row['sqlIndex'] === '') {
				$this->columnPackageIDs[$row['sqlTable']][$row['sqlColumn']] = $row['packageID'];
			}
			else if (substr($row['sqlIndex'], -3) === '_fk') {
				$this->foreignKeyPackageIDs[$row['sqlTable']][$row['sqlIndex']] = $row['packageID'];
			}
			else {
				$this->indexPackageIDs[$row['sqlTable']][$row['sqlIndex']] = $row['packageID'];
			}
		}
	}
	
	/**
	 * Adds the given index to the table.
	 * 
	 * @param	string				$tableName
	 * @param	DatabaseTableForeignKey		$foreignKey
	 */
	protected function addForeignKey($tableName, DatabaseTableForeignKey $foreignKey) {
		$this->dbEditor->addForeignKey($tableName, $foreignKey->getName(), $foreignKey->getData());
	}
	
	/**
	 * Adds the given index to the table.
	 *
	 * @param	string			$tableName
	 * @param	DatabaseTableIndex	$index
	 */
	protected function addIndex($tableName, DatabaseTableIndex $index) {
		$this->dbEditor->addIndex($tableName, $index->getName(), $index->getData());
	}
	
	/**
	 * Applies all of the previously determined changes to achieve the desired database layout.
	 * 
	 * @throws	SplitNodeException	if any change has been applied
	 */
	protected function applyChanges() {
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
		
		$columnTables = array_unique(array_merge(
			array_keys($this->columnsToAdd),
			array_keys($this->columnsToAlter),
			array_keys($this->columnsToDrop)
		));
		foreach ($columnTables as $tableName) {
			$appliedAnyChange = true;
			
			$columnsToAdd = $this->columnsToAdd[$tableName] ?? [];
			$columnsToAlter = $this->columnsToAlter[$tableName] ?? [];
			$columnsToDrop = $this->columnsToDrop[$tableName] ?? [];
			
			foreach ($columnsToAdd as $column) {
				$this->prepareColumnLog($tableName, $column);
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
			
			foreach ($columnsToDrop as $column) {
				$this->deleteColumnLog($tableName, $column);
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
		
		foreach ($this->foreignKeysToDrop as $tableName => $foreignKeys) {
			foreach ($foreignKeys as $foreignKey) {
				$appliedAnyChange = true;
				
				$this->dropForeignKey($tableName, $foreignKey);
				$this->deleteForeignKeyLog($tableName, $foreignKey);
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
		
		foreach ($this->indicesToDrop as $tableName => $indices) {
			foreach ($indices as $index) {
				$appliedAnyChange = true;
				
				$this->dropIndex($tableName, $index);
				$this->deleteIndexLog($tableName, $index);
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
	 * @param	string			$tableName
	 * @param	IDatabaseTableColumn[]	$addedColumns
	 * @param	IDatabaseTableColumn[]	$alteredColumns
	 * @param	IDatabaseTableColumn[]	$droppedColumns
	 */
	protected function applyColumnChanges($tableName, array $addedColumns, array $alteredColumns, array $droppedColumns) {
		$dropForeignKeys = [];
		
		$columnData = [];
		foreach ($droppedColumns as $droppedColumn) {
			$columnData[$droppedColumn->getName()] = [
				'action' => 'drop'
			];
			
			foreach ($this->getExistingTable($tableName)->getForeignKeys() as $foreignKey) {
				if (in_array($droppedColumn->getName(), $foreignKey->getColumns())) {
					$dropForeignKeys[] = $foreignKey->getName();
				}
			}
		}
		foreach ($addedColumns as $addedColumn) {
			$columnData[$addedColumn->getName()] = [
				'action' => 'add',
				'data' => $addedColumn->getData()
			];
		}
		foreach ($alteredColumns as $alteredColumn) {
			$columnData[$alteredColumn->getName()] = [
				'action' => 'alter',
				'data' => $alteredColumn->getData(),
				'oldColumnName' => $alteredColumn->getName()
			];
		}
		
		if (!empty($columnData)) {
			foreach ($dropForeignKeys as $foreignKey) {
				$this->dbEditor->dropForeignKey($tableName, $foreignKey);
			}
			
			$this->dbEditor->alterColumns($tableName, $columnData);
		}
	}
	
	/**
	 * Calculates all of the necessary changes to be executed.
	 */
	protected function calculateChanges() {
		foreach ($this->tables as $table) {
			$tableName = $table->getName();
			
			if ($table->willBeDropped()) {
				if (in_array($tableName, $this->existingTableNames)) {
					$this->tablesToDrop[] = $table;
					
					$this->splitNodeMessage .= "Dropped table '{$tableName}'.";
					break;
				}
				else if (isset($this->tablePackageIDs[$tableName])) {
					$this->deleteTableLog($table);
				}
			}
			else if (!in_array($tableName, $this->existingTableNames)) {
				if ($table instanceof PartialDatabaseTable) {
					throw new \LogicException("Partial table '{$tableName}' cannot be created.");
				}
				
				$this->tablesToCreate[] = $table;
				
				$this->splitNodeMessage .= "Created table '{$tableName}'.";
				break;
			}
			else {
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
						}
						else if (isset($this->columnPackageIDs[$tableName][$column->getName()])) {
							$this->deleteColumnLog($tableName, $column);
						}
					}
					else if (!isset($existingColumns[$column->getName()])) {
						if (!isset($this->columnsToAdd[$tableName])) {
							$this->columnsToAdd[$tableName] = [];
						}
						$this->columnsToAdd[$tableName][] = $column;
					}
					else if ($this->diffColumns($existingColumns[$column->getName()], $column)) {
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
						if (empty(array_diff($foreignKey->getData(), $existingForeignKey->getData()))) {
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
							
							$this->splitNodeMessage .= "Dropped foreign key '{$tableName}." . implode(',', $foreignKey->getColumns()) . "'.";
							break 2;
						}
						else if (isset($this->foreignKeyPackageIDs[$tableName][$foreignKey->getName()])) {
							$this->deleteForeignKeyLog($tableName, $foreignKey);
						}
					}
					else if ($matchingExistingForeignKey === null) {
						if (!isset($this->foreignKeysToAdd[$tableName])) {
							$this->foreignKeysToAdd[$tableName] = [];
						}
						$this->foreignKeysToAdd[$tableName][] = $foreignKey;
						
						$this->splitNodeMessage .= "Added foreign key '{$tableName}." . implode(',', $foreignKey->getColumns()) . "'.";
						break 2;
					}
				}
				
				$existingIndices = $existingTable->getIndices();
				foreach ($table->getIndices() as $index) {
					$matchingExistingIndex = null;
					foreach ($existingIndices as $existingIndex) {
						if (empty(array_diff($index->getData(), $existingIndex->getData()))) {
							$matchingExistingIndex = $existingIndex;
							break;
						}
					}
					
					if ($index->willBeDropped()) {
						if ($matchingExistingIndex !== null) {
							if (!isset($this->indicesToDrop[$tableName])) {
								$this->indicesToDrop[$tableName] = [];
							}
							$this->indicesToDrop[$tableName][] = $index;
							
							$this->splitNodeMessage .= "Dropped index '{$tableName}." . implode(',', $index->getColumns()) . "'.";
							break 2;
						}
						else if (isset($this->indexPackageIDs[$tableName][$index->getName()])) {
							$this->deleteIndexLog($tableName, $index);
						}
					}
					else if ($matchingExistingIndex === null) {
						if (!isset($this->indicesToAdd[$tableName])) {
							$this->indicesToAdd[$tableName] = [];
						}
						$this->indicesToAdd[$tableName][] = $index;
						
						$this->splitNodeMessage .= "Added index '{$tableName}." . implode(',', $index->getColumns()) . "'.";
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
	protected function checkPendingLogEntries() {
		$sql = "SELECT	*
			FROM	wcf" . WCF_N . "_package_installation_sql_log
			WHERE	packageID = ?
				AND isDone = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$this->package->packageID, 0]);
		
		$doneEntries = $undoneEntries = [];
		while ($row = $statement->fetchArray()) {
			// table
			if ($row['sqlIndex'] === '' && $row['sqlColumn'] === '') {
				if (in_array($row['sqlTable'], $this->existingTableNames)) {
					$doneEntries[] = $row;
				}
				else {
					$undoneEntries[] = $row;
				}
			}
			// column
			else if ($row['sqlIndex'] === '') {
				if (isset($this->getExistingTable($row['sqlTable'])->getColumns()[$row['sqlColumn']])) {
					$doneEntries[] = $row;
				}
				else {
					$undoneEntries[] = $row;
				}
			}
			// foreign key
			else if (substr($row['sqlIndex'], -3) === '_fk') {
				if (isset($this->getExistingTable($row['sqlTable'])->getForeignKeys()[$row['sqlIndex']])) {
					$doneEntries[] = $row;
				}
				else {
					$undoneEntries[] = $row;
				}
			}
			// index
			else {
				if (isset($this->getExistingTable($row['sqlTable'])->getIndices()[$row['sqlIndex']])) {
					$doneEntries[] = $row;
				}
				else {
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
	 * Creates the given table.
	 * 
	 * @param	DatabaseTable		$table
	 */
	protected function createTable(DatabaseTable $table) {
		$columnData = array_map(function(IDatabaseTableColumn $column) {
			return [
				'data' => $column->getData(),
				'name' => $column->getName()
			];
		}, $table->getColumns());
		$indexData = array_map(function(DatabaseTableIndex $index) {
			return [
				'data' => $index->getData(),
				'name' => $index->getName()
			];
		}, $table->getIndices());
		
		$this->dbEditor->createTable($table->getName(), $columnData, $indexData);
		
		foreach ($table->getForeignKeys() as $foreignKey) {
			$this->dbEditor->addForeignKey($table->getName(), $foreignKey->getName(), $foreignKey->getData());
		}
	}
	
	/**
	 * Deletes the log entry for the given column.
	 * 
	 * @param	string				$tableName
	 * @param	IDatabaseTableColumn		$column
	 */
	protected function deleteColumnLog($tableName, IDatabaseTableColumn $column) {
		$this->deleteLog(['sqlTable' => $tableName, 'sqlColumn' => $column->getName()]);
	}
	
	/**
	 * Deletes the log entry for the given foreign key.
	 *
	 * @param	string				$tableName
	 * @param	DatabaseTableForeignKey		$foreignKey
	 */
	protected function deleteForeignKeyLog($tableName, DatabaseTableForeignKey $foreignKey) {
		$this->deleteLog(['sqlTable' => $tableName, 'sqlIndex' => $foreignKey->getName()]);
	}
	
	/**
	 * Deletes the log entry for the given index.
	 * 
	 * @param	string			$tableName
	 * @param	DatabaseTableIndex	$index
	 */
	protected function deleteIndexLog($tableName, DatabaseTableIndex $index) {
		$this->deleteLog(['sqlTable' => $tableName, 'sqlIndex' => $index->getName()]);
	}
	
	/**
	 * Deletes a log entry.
	 * 
	 * @param	array	$data
	 */
	protected function deleteLog(array $data) {
		$sql = "DELETE FROM	wcf" . WCF_N . "_package_installation_sql_log
			WHERE		packageID = ?
					AND sqlTable = ?
					AND sqlColumn = ?
					AND sqlIndex = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		$statement->execute([
			$this->package->packageID,
			$data['sqlTable'],
			$data['sqlColumn'] ?? '',
			$data['sqlIndex'] ?? ''
		]);
	}
	
	/**
	 * Deletes all log entry related to the given table.
	 * 
	 * @param	DatabaseTable	$table
	 */
	protected function deleteTableLog(DatabaseTable $table) {
		$sql = "DELETE FROM	wcf" . WCF_N . "_package_installation_sql_log
			WHERE		packageID = ?
					AND sqlTable = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		$statement->execute([
			$this->package->packageID,
			$table->getName()
		]);
	}
	
	/**
	 * Returns `true` if the two columns differ.
	 * 
	 * @param	IDatabaseTableColumn	$oldColumn
	 * @param	IDatabaseTableColumn	$newColumn
	 * @return	bool
	 */
	protected function diffColumns(IDatabaseTableColumn $oldColumn, IDatabaseTableColumn $newColumn) {
		if (!empty(array_diff($oldColumn->getData(), $newColumn->getData()))) {
			return true;
		}
		
		// default type has to be checked with a strict check to differentiate between having
		// no default value (`null`) and having an empty string as default value
		return $oldColumn->getDefaultValue() !== $newColumn->getDefaultValue();
	}
	
	/**
	 * Drops the given foreign key.
	 * 
	 * @param	string				$tableName
	 * @param	DatabaseTableForeignKey		$foreignKey
	 */
	protected function dropForeignKey($tableName, DatabaseTableForeignKey $foreignKey) {
		$this->dbEditor->dropForeignKey($tableName, $foreignKey->getName());
		$this->dbEditor->dropIndex($tableName, $foreignKey->getName());
	}
	
	/**
	 * Drops the given index.
	 * 
	 * @param	string			$tableName
	 * @param	DatabaseTableIndex	$index
	 */
	protected function dropIndex($tableName, DatabaseTableIndex $index) {
		$this->dbEditor->dropIndex($tableName, $index->getName());
	}
	
	/**
	 * Drops the given table.
	 * 
	 * @param	DatabaseTable		$table
	 */
	protected function dropTable(DatabaseTable $table) {
		$this->dbEditor->dropTable($table->getName());
	}
	
	/**
	 * Finalizes the log entry for the creation of the given column.
	 * 
	 * @param	string			$tableName
	 * @param	IDatabaseTableColumn	$column
	 */
	protected function finalizeColumnLog($tableName, IDatabaseTableColumn $column) {
		$this->finalizeLog(['sqlTable' => $tableName, 'sqlColumn' => $column->getName()]);
	}
	
	/**
	 * Finalizes the log entry for adding the given index.
	 * 
	 * @param	string				$tableName
	 * @param	DatabaseTableForeignKey		$foreignKey
	 */
	protected function finalizeForeignKeyLog($tableName, DatabaseTableForeignKey $foreignKey) {
		$this->finalizeLog(['sqlTable' => $tableName, 'sqlIndex' => $foreignKey->getName()]);
	}
	
	/**
	 * Finalizes the log entry for adding the given index.
	 *
	 * @param	string			$tableName
	 * @param	DatabaseTableIndex	$index
	 */
	protected function finalizeIndexLog($tableName, DatabaseTableIndex $index) {
		$this->finalizeLog(['sqlTable' => $tableName, 'sqlIndex' => $index->getName()]);
	}
	
	/**
	 * Finalizes a log entry after the relevant change has been executed.
	 * 
	 * @param	array	$data
	 */
	protected function finalizeLog(array $data) {
		$sql = "UPDATE	wcf" . WCF_N . "_package_installation_sql_log
			SET	isDone = ?
			WHERE	packageID = ?
				AND sqlTable = ?
				AND sqlColumn = ?
				AND sqlIndex = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		$statement->execute([
			1,
			$this->package->packageID,
			$data['sqlTable'],
			$data['sqlColumn'] ?? '',
			$data['sqlIndex'] ?? ''
		]);
	}
	
	/**
	 * Finalizes the log entry for the creation of the given table.
	 * 
	 * @param	DatabaseTable	$table
	 */
	protected function finalizeTableLog(DatabaseTable $table) {
		$this->finalizeLog(['sqlTable' => $table->getName()]);
	}
	
	/**
	 * Returns the id of the package to with the given column belongs to. If there is no specific
	 * log entry for the given column, the table log is checked and the relevant package id of
	 * the whole table is returned. If the package of the table is also unknown, `null` is returned.
	 * 
	 * @param	DatabaseTable		$table
	 * @param	IDatabaseTableColumn	$column
	 * @return	null|int
	 */
	protected function getColumnPackageID(DatabaseTable $table, IDatabaseTableColumn $column) {
		if (isset($this->columnPackageIDs[$table->getName()][$column->getName()])) {
			return $this->columnPackageIDs[$table->getName()][$column->getName()];
		}
		else if (isset($this->tablePackageIDs[$table->getName()])) {
			return $this->tablePackageIDs[$table->getName()];
		}
		
		return null;
	}
	
	/**
	 * Returns the `DatabaseTable` object for the table with the given name.
	 * 
	 * @param	string		$tableName
	 * @return	DatabaseTable
	 */
	protected function getExistingTable($tableName) {
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
	 * @param	DatabaseTable			$table
	 * @param	DatabaseTableForeignKey		$foreignKey
	 * @return	null|int
	 */
	protected function getForeignKeyPackageID(DatabaseTable $table, DatabaseTableForeignKey $foreignKey) {
		if (isset($this->foreignKeyPackageIDs[$table->getName()][$foreignKey->getName()])) {
			return $this->foreignKeyPackageIDs[$table->getName()][$foreignKey->getName()];
		}
		else if (isset($this->tablePackageIDs[$table->getName()])) {
			return $this->tablePackageIDs[$table->getName()];
		}
		
		return null;
	}
	
	/**
	 * Returns the id of the package to with the given index belongs to. If there is no specific
	 * log entry for the given index, the table log is checked and the relevant package id of
	 * the whole table is returned. If the package of the table is also unknown, `null` is returned.
	 * 
	 * @param	DatabaseTable		$table
	 * @param	DatabaseTableIndex	$index
	 * @return	null|int
	 */
	protected function getIndexPackageID(DatabaseTable $table, DatabaseTableIndex $index) {
		if (isset($this->indexPackageIDs[$table->getName()][$index->getName()])) {
			return $this->indexPackageIDs[$table->getName()][$index->getName()];
		}
		else if (isset($this->tablePackageIDs[$table->getName()])) {
			return $this->tablePackageIDs[$table->getName()];
		}
		
		return null;
	}
	
	/**
	 * Prepares the log entry for the creation of the given column.
	 * 
	 * @param	string			$tableName
	 * @param	IDatabaseTableColumn	$column
	 */
	protected function prepareColumnLog($tableName, IDatabaseTableColumn $column) {
		$this->prepareLog(['sqlTable' => $tableName, 'sqlColumn' => $column->getName()]);
	}
	
	/**
	 * Prepares the log entry for adding the given foreign key.
	 * 
	 * @param	string				$tableName
	 * @param	DatabaseTableForeignKey		$foreignKey
	 */
	protected function prepareForeignKeyLog($tableName, DatabaseTableForeignKey $foreignKey) {
		$this->prepareLog(['sqlTable' => $tableName, 'sqlIndex' => $foreignKey->getName()]);
	}
	
	/**
	 * Prepares the log entry for adding the given index.
	 *
	 * @param	string			$tableName
	 * @param	DatabaseTableIndex	$index
	 */
	protected function prepareIndexLog($tableName, DatabaseTableIndex $index) {
		$this->prepareLog(['sqlTable' => $tableName, 'sqlIndex' => $index->getName()]);
	}
	
	/**
	 * Prepares a log entry before the relevant change has been executed.
	 *
	 * @param	array	$data
	 */
	protected function prepareLog(array $data) {
		$sql = "INSERT INTO	wcf" . WCF_N . "_package_installation_sql_log
					(packageID, sqlTable, sqlColumn, sqlIndex, isDone)
			VALUES		(?, ?, ?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		$statement->execute([
			$this->package->packageID,
			$data['sqlTable'],
			$data['sqlColumn'] ?? '',
			$data['sqlIndex'] ?? '',
			0
		]);
	}
	
	/**
	 * Prepares the log entry for the creation of the given table.
	 *
	 * @param	DatabaseTable	$table
	 */
	protected function prepareTableLog(DatabaseTable $table) {
		$this->prepareLog(['sqlTable' => $table->getName()]);
	}
	
	/**
	 * Processes all tables and updates the current table layouts to match the specified layouts. 
	 * 
	 * @throws	\RuntimeException	if validation of the required layout changes fails
	 */
	public function process() {
		$this->checkPendingLogEntries();
		
		$errors = $this->validate();
		if (!empty($errors)) {
			throw new \RuntimeException(WCF::getLanguage()->getDynamicVariable('wcf.acp.package.error.databaseChange', [
				'errors' => $errors
			]));
		}
		
		$this->calculateChanges();
		
		$this->applyChanges();
	}
	
	/**
	 * Checks if the relevant table layout changes can be executed and returns an array with information
	 * on all validation errors.
	 * 
	 * @return	array
	 */
	public function validate() {
		$errors = [];
		foreach ($this->tables as $table) {
			if ($table->willBeDropped()) {
				if (in_array($table->getName(), $this->existingTableNames)) {
					if (!isset($this->tablePackageIDs[$table->getName()])) {
						$errors[] = [
							'tableName' => $table->getName(),
							'type' => 'unregisteredTableDrop'
						];
					}
					else if ($this->tablePackageIDs[$table->getName()] !== $this->package->packageID) {
						$errors[] = [
							'tableName' => $table->getName(),
							'type' => 'foreignTableDrop'
						];
					}
				}
			}
			else if (in_array($table->getName(), $this->existingTableNames)) {
				if (!isset($this->tablePackageIDs[$table->getName()])) {
					$errors[] = [
						'tableName' => $table->getName(),
						'type' => 'unregisteredTableChange'
					];
				}
				else {
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
										'type' => 'foreignColumnDrop'
									];
								}
							}
							else if ($columnPackageID !== $this->package->packageID) {
								$errors[] = [
									'columnName' => $column->getName(),
									'tableName' => $table->getName(),
									'type' => 'foreignColumnChange'
								];
							}
						}
					}
					
					foreach ($table->getIndices() as $index) {
						foreach ($existingIndices as $existingIndex) {
							if (empty(array_diff($index->getData(), $existingIndex->getData()))) {
								if ($index->willBeDropped()) {
									if ($this->getIndexPackageID($table, $index) !== $this->package->packageID) {
										$errors[] = [
											'columnNames' => implode(',', $existingIndex->getColumns()),
											'tableName' => $table->getName(),
											'type' => 'foreignIndexDrop'
										];
									}
								}
								
								continue 2;
							}
						}
					}
					
					foreach ($table->getForeignKeys() as $foreignKey) {
						foreach ($existingForeignKeys as $existingForeignKey) {
							if (empty(array_diff($foreignKey->getData(), $existingForeignKey->getData()))) {
								if ($foreignKey->willBeDropped()) {
									if ($this->getForeignKeyPackageID($table, $foreignKey) !== $this->package->packageID) {
										$errors[] = [
											'columnNames' => implode(',', $existingForeignKey->getColumns()),
											'tableName' => $table->getName(),
											'type' => 'foreignForeignKeyDrop'
										];
									}
								}
								
								continue 2;
							}
						}
					}
				}
			}
		}
		
		return $errors;
	}
}
