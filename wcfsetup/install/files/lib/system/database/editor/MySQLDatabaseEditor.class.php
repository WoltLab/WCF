<?php
namespace wcf\system\database\editor;
use wcf\system\database\exception\DatabaseQueryExecutionException;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\Regex;

/**
 * Database editor implementation for MySQL4.1 or higher.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Database\Editor
 */
class MySQLDatabaseEditor extends DatabaseEditor {
	/**
	 * @inheritDoc
	 */
	public function getTableNames() {
		$existingTables = [];
		$sql = "SHOW TABLES FROM `".$this->dbObj->getDatabaseName()."`";
		$statement = $this->dbObj->prepareStatement($sql);
		$statement->execute();
		while ($row = $statement->fetchArray(\PDO::FETCH_NUM)) {
			$existingTables[] = $row[0];
		}
		return $existingTables;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getColumns($tableName) {
		$columns = [];
		$regex = new Regex('([a-z]+)\((.+)\)', Regex::CASE_INSENSITIVE);
		
		$sql = "SHOW COLUMNS FROM `".$tableName."`";
		$statement = $this->dbObj->prepareStatement($sql);
		$statement->execute();
		while ($row = $statement->fetchArray()) {
			$regex->match($row['Type']);
			$typeMatches = $regex->getMatches();
			
			$type = $row['Type'];
			$length = '';
			$decimals = '';
			$enumValues = '';
			if (!empty($typeMatches)) {
				$type = $typeMatches[1];
				
				switch ($type) {
					case 'enum':
					case 'set':
						$enumValues = $typeMatches[2];
						break;
						
					case 'decimal':
					case 'double':
					case 'float':
						$pieces = explode(',', $typeMatches[2]);
						switch (count($pieces)) {
							case 1:
								$length = $pieces[0];
								break;
								
							case 2:
								list($length, $decimals) = $pieces;
								break;
						}
						
						break;
						
					default:
						if ($typeMatches[2] == (int)$typeMatches[2]) {
							$length = $typeMatches[2];
						}
						break;
				}
			}
			
			$columns[] = ['name' => $row['Field'], 'data' => [
				'type' => $type,
				'length' => $length,
				'notNull' => $row['Null'] == 'YES' ? false : true,
				'key' => ($row['Key'] == 'PRI') ? 'PRIMARY' : (($row['Key'] == 'UNI') ? 'UNIQUE' : ''),
				'default' => $row['Default'],
				'autoIncrement' => $row['Extra'] == 'auto_increment' ? true : false,
				'enumValues' => $enumValues,
				'decimals' => $decimals
			]];
		}
		
		return $columns;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getForeignKeys($tableName) {
		$sql = "SELECT	CONSTRAINT_NAME, DELETE_RULE, UPDATE_RULE
			FROM	INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS
			WHERE	CONSTRAINT_SCHEMA = ?
				AND TABLE_NAME = ?";
		$statement = $this->dbObj->prepareStatement($sql);
		$statement->execute([
			$this->dbObj->getDatabaseName(),
			$tableName
		]);
		$referentialConstraints = $statement->fetchAll(\PDO::FETCH_ASSOC);
		
		$validActions = ['CASCADE', 'SET NULL', 'NO ACTION'];
		
		$foreignKeys = [];
		foreach ($referentialConstraints as $information) {
			$foreignKeys[$information['CONSTRAINT_NAME']] = [
				'columns' => [],
				'referencedColumns' => [],
				'ON DELETE' => in_array($information['DELETE_RULE'], $validActions) ? $information['DELETE_RULE'] : null,
				'ON UPDATE' => in_array($information['UPDATE_RULE'], $validActions) ? $information['UPDATE_RULE'] : null
			];
		}
		
		if (empty($foreignKeys)) {
			return [];
		}
		
		$conditionBuilder = new PreparedStatementConditionBuilder();
		$conditionBuilder->add('CONSTRAINT_NAME IN (?)', [array_keys($foreignKeys)]);
		$conditionBuilder->add('TABLE_SCHEMA = ?', [$this->dbObj->getDatabaseName()]);
		$conditionBuilder->add('TABLE_NAME = ?', [$tableName]);
		
		$sql = "SELECT	CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
			FROM	INFORMATION_SCHEMA.KEY_COLUMN_USAGE
			" . $conditionBuilder;
		$statement = $this->dbObj->prepareStatement($sql);
		$statement->execute($conditionBuilder->getParameters());
		$keyColumnUsage = $statement->fetchAll(\PDO::FETCH_ASSOC);
		
		foreach ($keyColumnUsage as $information) {
			$foreignKeys[$information['CONSTRAINT_NAME']]['columns'][] = $information['COLUMN_NAME'];
			$foreignKeys[$information['CONSTRAINT_NAME']]['referencedColumns'][] = $information['REFERENCED_COLUMN_NAME'];
			$foreignKeys[$information['CONSTRAINT_NAME']]['referencedTable'] = $information['REFERENCED_TABLE_NAME'];
		}
		
		foreach ($foreignKeys as $keyName => $keyData) {
			$foreignKeys[$keyName]['columns'] = array_unique($foreignKeys[$keyName]['columns']);
			$foreignKeys[$keyName]['referencedColumns'] = array_unique($foreignKeys[$keyName]['referencedColumns']);
		}
		
		return $foreignKeys;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getIndexInformation($tableName) {
		$sql = "SHOW	INDEX
			FROM	`".$tableName."`";
		$statement = $this->dbObj->prepareStatement($sql);
		$statement->execute();
		$indices = $statement->fetchAll(\PDO::FETCH_ASSOC);
		
		$indexInformation = [];
		foreach ($indices as $index) {
			if (!isset($indexInformation[$index['Key_name']])) {
				$type = null;
				if ($index['Index_type'] === 'FULLTEXT') {
					$type = 'FULLTEXT';
				}
				else if ($index['Key_name'] === 'PRIMARY') {
					$type = 'PRIMARY';
				}
				else if ($index['Non_unique'] == 0) {
					$type = 'UNIQUE';
				}
				
				$indexInformation[$index['Key_name']] = [
					'columns' => [$index['Column_name']],
					'type' => $type
				];
			}
			else {
				$indexInformation[$index['Key_name']]['columns'][] = $index['Column_name'];
			}
		}
		
		return $indexInformation;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getIndices($tableName) {
		$indices = [];
		$sql = "SHOW INDEX FROM `".$tableName."`";
		$statement = $this->dbObj->prepareStatement($sql);
		$statement->execute();
		while ($row = $statement->fetchArray()) {
			$indices[] = $row['Key_name'];
		}
		
		return array_unique($indices);
	}
	
	/**
	 * @inheritDoc
	 */
	public function createTable($tableName, $columns, $indices = []) {
		$columnDefinition = $indexDefinition = '';
		
		// build column definition
		foreach ($columns as $column) {
			if (!empty($columnDefinition)) $columnDefinition .= ',';
			$columnDefinition .= $this->buildColumnDefinition($column['name'], $column['data']);
		}
		
		// build index definition
		$hasFulltextIndex = false;
		foreach ($indices as $index) {
			if (!empty($indexDefinition)) $indexDefinition .= ',';
			$indexDefinition .= $this->buildIndexDefinition($index['name'], $index['data']);
			if ($index['data']['type'] == 'FULLTEXT') $hasFulltextIndex = true;
		}
		
		// create table
		$sql = "CREATE TABLE `".$tableName."` (
				".$columnDefinition."
				".(!empty($indexDefinition) ? ',' : '')."
				".$indexDefinition."
			) ENGINE=".($hasFulltextIndex ? 'MyISAM' : 'InnoDB')." DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
		$statement = $this->dbObj->prepareStatement($sql);
		$statement->execute();
	}
	
	/**
	 * @inheritDoc
	 */
	public function dropTable($tableName) {
		$sql = "DROP TABLE IF EXISTS `".$tableName."`";
		$statement = $this->dbObj->prepareStatement($sql);
		$statement->execute();
	}
	
	/**
	 * @inheritDoc
	 */
	public function addColumn($tableName, $columnName, $columnData) {
		$sql = "ALTER TABLE `".$tableName."` ADD COLUMN ".$this->buildColumnDefinition($columnName, $columnData);
		$statement = $this->dbObj->prepareStatement($sql);
		$statement->execute();
	}
	
	/**
	 * @inheritDoc
	 */
	public function alterColumn($tableName, $oldColumnName, $newColumnName, $newColumnData) {
		$sql = "ALTER TABLE `".$tableName."` CHANGE COLUMN `".$oldColumnName."` ".$this->buildColumnDefinition($newColumnName, $newColumnData);
		$statement = $this->dbObj->prepareStatement($sql);
		$statement->execute();
	}
	
	/**
	 * @inheritDoc
	 */
	public function alterColumns($tableName, $alterData) {
		$queries = "";
		foreach ($alterData as $columnName => $data) {
			switch ($data['action']) {
				case 'add':
					$queries .= "ADD COLUMN {$this->buildColumnDefinition($columnName, $data['data'])},";
					break;
					
				case 'alter':
					$queries .= "CHANGE COLUMN `{$columnName}` {$this->buildColumnDefinition($data['oldColumnName'], $data['data'])},";
					break;
					
				case 'drop':
					$queries .= "DROP COLUMN `{$columnName}`,";
					break;
			}
		}
		
		$this->dbObj->prepareStatement("ALTER TABLE `{$tableName}` " . rtrim($queries, ','))->execute();
	}

	/**
	 * @inheritDoc
	 */
	public function dropColumn($tableName, $columnName) {
		try {
			$sql = "ALTER TABLE `".$tableName."` DROP COLUMN `".$columnName."`";
			$statement = $this->dbObj->prepareStatement($sql);
			$statement->execute();
		}
		catch (DatabaseQueryExecutionException $e) {
			if ($e->getCode() != '42000') {
				throw $e;
			}
			if (in_array($columnName, array_column($this->getColumns($tableName), 'name'))) {
				throw $e;
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function addIndex($tableName, $indexName, $indexData) {
		$sql = "ALTER TABLE `".$tableName."` ADD ".$this->buildIndexDefinition($indexName, $indexData);
		$statement = $this->dbObj->prepareStatement($sql);
		$statement->execute();
	}
	
	/**
	 * @inheritDoc
	 */
	public function addForeignKey($tableName, $indexName, $indexData) {
		$sql = "ALTER TABLE `".$tableName."` ADD";
		
		// add index name
		if (!empty($indexName)) $sql .= " CONSTRAINT `".$indexName."`";
		
		// add columns
		$sql .= " FOREIGN KEY (`".str_replace(',', '`,`', preg_replace('/\s+/', '', $indexData['columns']))."`)";
		
		// add referenced table name
		$sql .= " REFERENCES `".$indexData['referencedTable']."`";
		
		// add referenced columns
		$sql .= " (`".str_replace(',', '`,`', preg_replace('/\s+/', '', $indexData['referencedColumns']))."`)";
		
		// add operation and action
		if (!empty($indexData['operation'])) $sql .= " ON ".$indexData['operation']." ".$indexData['action'];
		if (!empty($indexData['ON DELETE'])) $sql .= " ON DELETE ".$indexData['ON DELETE'];
		if (!empty($indexData['ON UPDATE'])) $sql .= " ON UPDATE ".$indexData['ON UPDATE'];
		
		$statement = $this->dbObj->prepareStatement($sql);
		$statement->execute();
	}
	
	/**
	 * @inheritDoc
	 */
	public function dropIndex($tableName, $indexName) {
		try {
			$sql = "ALTER TABLE `".$tableName."` DROP INDEX `".$indexName."`";
			$statement = $this->dbObj->prepareStatement($sql);
			$statement->execute();
		}
		catch (DatabaseQueryExecutionException $e) {
			if ($e->getCode() != '42000') {
				throw $e;
			}
			if (in_array($indexName, $this->getIndices($tableName))) {
				throw $e;
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function dropPrimaryKey($tableName) {
		try {
			$sql = "ALTER TABLE ".$tableName." DROP PRIMARY KEY";
			$statement = $this->dbObj->prepareStatement($sql);
			$statement->execute();
		}
		catch (DatabaseQueryExecutionException $e) {
			if ($e->getCode() != '42000') {
				throw $e;
			}
			if (in_array("PRIMARY", $this->getIndices($tableName))) {
				throw $e;
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function dropForeignKey($tableName, $indexName) {
		try {
			$sql = "ALTER TABLE `".$tableName."` DROP FOREIGN KEY `".$indexName."`";
			$statement = $this->dbObj->prepareStatement($sql);
			$statement->execute();
		}
		catch (DatabaseQueryExecutionException $e) {
			if ($e->getCode() != '42000') {
				throw $e;
			}
			if (in_array($indexName, array_keys($this->getForeignKeys($tableName)))) {
				throw $e;
			}
		}
	}
	
	/**
	 * Builds a column definition for execution in a create table or alter table statement.
	 * 
	 * @param	string		$columnName
	 * @param	array		$columnData
	 * @return	string
	 */
	protected function buildColumnDefinition($columnName, $columnData) {
		// column name
		$definition = "`".$columnName."`";
		// column type
		$definition .= " ".$columnData['type'];
		
		// column length and decimals
		if (!empty($columnData['length'])) {
			$definition .= "(".$columnData['length'].(!empty($columnData['decimals']) ? ",".$columnData['decimals'] : "").")";
		}
		// enum / set
		if ($columnData['type'] == 'enum' && !empty($columnData['values'])) {
			$definition .= "(".$columnData['values'].")";
		}
		// not null / null
		if (!empty($columnData['notNull'])) $definition .= " NOT NULL";
		// default
		if (isset($columnData['default']) && $columnData['default'] !== '') $definition .= " DEFAULT ".$columnData['default'];
		// auto_increment
		if (!empty($columnData['autoIncrement'])) $definition .= " AUTO_INCREMENT";
		// key
		if (!empty($columnData['key'])) $definition .= " ".$columnData['key']." KEY";
		
		return $definition;
	}
	
	/**
	 * Builds a index definition for execution in a create table or alter table statement.
	 * 
	 * @param	string		$indexName
	 * @param	array		$indexData
	 * @return	string
	 */
	protected function buildIndexDefinition($indexName, $indexData) {
		// index type
		if ($indexData['type'] == 'PRIMARY') $definition = "PRIMARY KEY";
		else if ($indexData['type'] == 'UNIQUE') $definition = "UNIQUE KEY";
		else if ($indexData['type'] == 'FULLTEXT') $definition = "FULLTEXT KEY";
		else $definition = "KEY";
		
		// index name
		if (!empty($indexName)) $definition .= " `".$indexName."`";
		// columns
		$definition .= " (`".str_replace(',', '`,`', preg_replace('/\s+/', '', $indexData['columns']))."`)";
		
		return $definition;
	}
}
