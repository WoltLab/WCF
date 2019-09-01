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
	 * added columns grouped by the table they belong to
	 * @var	IDatabaseTableColumn[][]
	 */
	protected $addedColumns = [];
	
	/**
	 * added indices grouped by the table they belong to
	 * @var	DatabaseTableIndex[][]
	 */
	protected $addedIndices = [];
	
	/**
	 * added tables
	 * @var	DatabaseTable[]
	 */
	protected $addedTables = [];
	
	/**
	 * maps the registered database table column names to the ids of the packages they belong to
	 * @var	int[][]
	 */
	protected $columnPackageIDs = [];
	
	/**
	 * database editor to apply the relevant changes to the table layouts
	 * @var	DatabaseEditor
	 */
	protected $dbEditor;
	
	/**
	 * dropped columns grouped by the table they belong to
	 * @var	IDatabaseTableColumn[][]
	 */
	protected $droppedColumns = [];
	
	/**
	 * dropped indices grouped by the table they belong to
	 * @var	DatabaseTableIndex[][]|DatabaseTableForeignKey[][]
	 */
	protected $droppedIndices = [];
	
	/**
	 * dropped tables
	 * @var	DatabaseTable[]
	 */
	protected $droppedTables = [];
	
	/**
	 * list of all existing tables in the used database
	 * @var	string[]
	 */
	protected $existingTableNames = [];
	
	/**
	 * maps the registered database table index names to the ids of the packages they belong to
	 * @var	int[][]
	 */
	protected $indexPackageIDs = [];
	
	/**
	 * maps the registered database table foreign key names to the ids of the packages they belong to
	 * @var	int[][]
	 */
	protected $foreignKeyPackageIDs = [];
	
	/**
	 * is `true` if only one change will be handled per request
	 * @var	bool
	 */
	protected $oneChangePerRequest = true;
	
	/**
	 * package that wants to apply the changes
	 * @var	Package
	 */
	protected $package;
	
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
	 * Creates a new instance of `DatabaseTableChangeProcessor`.
	 * 
	 * @param	Package			$package
	 * @param	DatabaseTable[]		$tables
	 * @param	DatabaseEditor		$dbEditor
	 * @param	bool			$oneChangePerRequest
	 */
	public function __construct(Package $package, array $tables, DatabaseEditor $dbEditor, $oneChangePerRequest = true) {
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
		$this->oneChangePerRequest = $oneChangePerRequest;
		
		$this->existingTableNames = $dbEditor->getTableNames();
		
		$conditionBuilder = new PreparedStatementConditionBuilder();
		$conditionBuilder->add('sqlTable IN (?)', [$tableNames]);
		
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_package_installation_sql_log
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
	 * Creates the given table.
	 * 
	 * @param	DatabaseTable		$table
	 * @throws	SplitNodeException
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
		
		$this->addedTables[] = $table;
		
		if ($this->oneChangePerRequest) {
			$this->logChanges();
			
			throw new SplitNodeException("Created table '{$table->getName()}'.");
		}
	}
	
	/**
	 * Drops the given table.
	 * 
	 * @param	DatabaseTable		$table
	 * @throws	SplitNodeException
	 */
	protected function dropTable(DatabaseTable $table) {
		$this->dbEditor->dropTable($table->getName());
		
		$this->droppedTables[] = $table;
		
		if ($this->oneChangePerRequest) {
			$this->logChanges();
			
			throw new SplitNodeException("Dropped table '{$table->getName()}'.");
		}
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
	 * Logs all of the executed changes.
	 */
	protected function logChanges() {
		if (!empty($this->droppedTables)) {
			$sql = "DELETE FROM	wcf".WCF_N."_package_installation_sql_log
				WHERE		sqlTable = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			WCF::getDB()->beginTransaction();
			foreach ($this->droppedTables as $table) {
				$statement->execute([$table->getName()]);
			}
			WCF::getDB()->commitTransaction();
		}
		
		if (!empty($this->droppedColumns)) {
			$sql = "DELETE FROM	wcf".WCF_N."_package_installation_sql_log
				WHERE		sqlTable = ?
						AND sqlColumn = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			WCF::getDB()->beginTransaction();
			foreach ($this->droppedColumns as $tableName => $columns) {
				foreach ($columns as $column) {
					$statement->execute([$tableName, $column->getName()]);
				}
			}
			WCF::getDB()->commitTransaction();
		}
		
		if (!empty($this->droppedIndices)) {
			$sql = "DELETE FROM	wcf".WCF_N."_package_installation_sql_log
				WHERE		sqlTable = ?
						AND sqlIndex = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			WCF::getDB()->beginTransaction();
			foreach ($this->droppedIndices as $tableName => $indices) {
				foreach ($indices as $index) {
					$statement->execute([$tableName, $index->getName()]);
				}
			}
			WCF::getDB()->commitTransaction();
		}
		
		$insertionData = [];
		foreach ($this->addedTables as $table) {
			$insertionData[] = ['sqlTable' => $table->getName()];
		}
		
		foreach ($this->addedColumns as $tableName => $columns) {
			foreach ($columns as $column) {
				$insertionData[] = ['sqlTable' => $tableName, 'sqlColumn' => $column->getName()];
			}
		}
		
		foreach ($this->addedIndices as $tableName => $indices) {
			foreach ($indices as $index) {
				$insertionData[] = ['sqlTable' => $tableName, 'sqlIndex' => $index->getName()];
			}
		}
		
		if (!empty($insertionData)) {
			$sql = "INSERT INTO	wcf".WCF_N."_package_installation_sql_log
						(packageID, sqlTable, sqlColumn, sqlIndex)
				VALUES		(?, ?, ?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			WCF::getDB()->beginTransaction();
			foreach ($insertionData as $data) {
				$statement->execute([
					$this->package->packageID,
					$data['sqlTable'],
					$data['sqlColumn'] ?? '',
					$data['sqlIndex'] ?? ''
				]);
			}
			WCF::getDB()->commitTransaction();
		}
	}
	
	/**
	 * Processes all tables and updates the current table layouts to match the specified layouts. 
	 * 
	 * @throws	\RuntimeException	if validation of the required layout changes fails
	 */
	public function process() {
		$errors = $this->validate();
		if (!empty($errors)) {
			throw new \RuntimeException(WCF::getLanguage()->getDynamicVariable('wcf.acp.package.error.databaseChange', [
				'errors' => $errors
			]));
		}
		
		foreach ($this->tables as $table) {
			if ($table->willBeDropped()) {
				if (in_array($table->getName(), $this->existingTableNames)) {
					$this->dropTable($table);
				}
			}
			else if (!in_array($table->getName(), $this->existingTableNames)) {
				$this->createTable($table);
			}
			else {
				// calculate difference between tables
				$existingTable = DatabaseTable::createFromExistingTable($this->dbEditor, $table->getName());
				$existingColumns = $existingTable->getColumns();
				$existingForeignKeys = $existingTable->getForeignKeys();
				$existingIndices = $existingTable->getIndices();
				
				$addedColumns = $alteredColumns = $droppedColumns = [];
				foreach ($table->getColumns() as $column) {
					if (!isset($existingColumns[$column->getName()]) && !$column->willBeDropped()) {
						$addedColumns[$column->getName()] = $column;
					}
					else if (isset($existingColumns[$column->getName()])) {
						if ($column->willBeDropped()) {
							$droppedColumns[$column->getName()] = $column;
						}
						else if (!empty(array_diff($column->getData(), $existingColumns[$column->getName()]->getData()))) {
							$alteredColumns[$column->getName()] = $column;
						}
					}
				}
				
				$this->processColumns($table, $addedColumns, $alteredColumns, $droppedColumns);
				
				$addedForeignKeys = $droppedForeignKeys = [];
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
							$droppedForeignKeys[$foreignKey->getName()] = $foreignKey;
						}
					}
					else if ($matchingExistingForeignKey === null) {
						$addedForeignKeys[$foreignKey->getName()] = $foreignKey;
					}
				}
				
				$this->processForeignKeys($table, $addedForeignKeys, $droppedForeignKeys);
				
				$addedIndices = $droppedIndices = [];
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
							$droppedIndices[$index->getName()] = $index;
						}
					}
					else if ($matchingExistingIndex === null) {
						$addedIndices[$index->getName()] = $index;
					}
				}
				
				$this->processIndices($table, $addedIndices, $droppedIndices);
			}
		}
		
		$this->logChanges();
	}
	
	/**
	 * Adds, alters and drops the given columns.
	 * 
	 * @param	DatabaseTable			$table
	 * @param	IDatabaseTableColumn[]		$addedColumns
	 * @param	IDatabaseTableColumn[]		$alteredColumns
	 * @param	IDatabaseTableColumn[]		$droppedColumns
	 * @throws	SplitNodeException
	 */
	protected function processColumns(DatabaseTable $table, array $addedColumns, array $alteredColumns, array $droppedColumns) {
		$columnData = [];
		foreach ($droppedColumns as $droppedColumn) {
			$columnData[$droppedColumn->getName()] = [
				'action' => 'drop'
			];
			
			$this->droppedColumns[$table->getName()][$droppedColumn->getName()] = $droppedColumn;
		}
		foreach ($addedColumns as $addedColumn) {
			$columnData[$addedColumn->getName()] = [
				'action' => 'add',
				'data' => $addedColumn->getData()
			];
			
			if ($this->tablePackageIDs[$table->getName()] !== $this->package->packageID) {
				$this->addedColumns[$table->getName()][$addedColumn->getName()] = $addedColumn;
			}
		}
		foreach ($alteredColumns as $alteredColumn) {
			$columnData[$alteredColumn->getName()] = [
				'action' => 'alter',
				'data' => $alteredColumn->getData(),
				'oldColumnName' => $alteredColumn->getName()
			];
		}
		
		if (!empty($columnData)) {
			$this->dbEditor->alterColumns($table->getName(), $columnData);
			
			if ($this->oneChangePerRequest) {
				$this->logChanges();
				
				throw new SplitNodeException("Altered columns of table '{$table->getName()}'.");
			}
		}
	}
	
	/**
	 * Adds and drops the given foreign keys.
	 * 
	 * @param	DatabaseTable			$table
	 * @param	DatabaseTableForeignKey[]	$addedForeignKeys
	 * @param	DatabaseTableForeignKey[]	$droppedForeignKeys
	 * @throws	SplitNodeException
	 */
	protected function processForeignKeys(DatabaseTable $table, array $addedForeignKeys, array $droppedForeignKeys) {
		if (empty($addedForeignKeys) && empty($droppedForeignKeys)) {
			return;
		}
		
		foreach ($addedForeignKeys as $addedForeignKey) {
			if ($this->tablePackageIDs[$table->getName()] !== $this->package->packageID) {
				$this->addedIndices[$table->getName()][$addedForeignKey->getName()] = $addedForeignKey;
			}
			
			$this->dbEditor->addForeignKey($table->getName(), $addedForeignKey->getName(), $addedForeignKey->getData());
			
			if ($this->oneChangePerRequest) {
				$this->logChanges();
				
				throw new SplitNodeException("Added foreign key '{$table->getName()}." . implode(',', $addedForeignKey->getColumns()) . "'");
			}
		}
		
		foreach ($droppedForeignKeys as $droppedForeignKey) {
			$this->droppedIndices[$table->getName()][$droppedForeignKey->getName()] = $droppedForeignKey;
			
			$this->dbEditor->dropForeignKey($table->getName(), $droppedForeignKey->getName());
			
			if ($this->oneChangePerRequest) {
				$this->logChanges();
				
				throw new SplitNodeException("Dropped foreign key '{$table->getName()}." . implode(',', $droppedForeignKey->getColumns()) . "' ({$droppedForeignKey->getName()})");
			}
		}
	}
	
	/**
	 * Adds and drops the given indices.
	 * 
	 * @param	DatabaseTable		$table
	 * @param	DatabaseTableIndex[]	$addedIndices
	 * @param	DatabaseTableIndex[]	$droppedIndices
	 * @throws	SplitNodeException
	 */
	protected function processIndices(DatabaseTable $table, array $addedIndices, array $droppedIndices) {
		if (empty($addedIndices) && empty($droppedIndices)) {
			return;
		}
		
		foreach ($addedIndices as $addedIndex) {
			if ($this->tablePackageIDs[$table->getName()] !== $this->package->packageID) {
				$this->addedIndices[$table->getName()][$addedIndex->getName()] = $addedIndex;
			}
			
			$this->dbEditor->addIndex($table->getName(), $addedIndex->getName(), $addedIndex->getData());
			
			if ($this->oneChangePerRequest) {
				$this->logChanges();
				
				throw new SplitNodeException("Added index '{$table->getName()}." . implode(',', $addedIndex->getColumns()) . "'");
			}
		}
		
		foreach ($droppedIndices as $droppedIndex) {
			$this->droppedIndices[$table->getName()][$droppedIndex->getName()] = $droppedIndex;
			
			$this->dbEditor->dropIndex($table->getName(), $droppedIndex->getName());
			
			if ($this->oneChangePerRequest) {
				$this->logChanges();
				
				throw new SplitNodeException("Dropped index '{$table->getName()}." . implode(',', $droppedIndex->getColumns()) . "'");
			}
		}
	}
	
	/**
	 * Checks if the relevant table layout changes can be executed and returns an array with information
	 * on any validation error.
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
