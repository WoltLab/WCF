<?php
namespace wcf\system\database\editor;
use wcf\system\database\DatabaseException;
use wcf\util\ArrayUtil;

/**
 * Database editor implementation for PostgreSQL 8.0 or higher.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Database\Editor
 */
class PostgreSQLDatabaseEditor extends DatabaseEditor {
	/**
	 * @inheritDoc
	 */
	public function getTableNames() {
		$existingTables = [];
		$sql = "SELECT		tablename
			FROM		pg_catalog.pg_tables
			WHERE		schemaname = 'public'
			ORDER BY	tablename";
		$statement = $this->dbObj->prepareStatement($sql);
		$statement->execute();
		while ($row = $statement->fetchArray()) {
			$existingTables[] = $row['tablename'];
		}
		return $existingTables;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getColumns($tableName) {
		$columns = [];
		$sql = "SELECT	pg_attribute.*, pg_type.typname, pg_constraint.contype, pg_attribute.adsrc
			FROM	pg_attribute,
				pg_class,
				pg_type
			LEFT JOIN	pg_constraint ON (pg_constraint.conrelid = pg_class.oid)
			LEFT JOIN	pg_attrdef ON (pg_attrdef.adrelid = pg_attribute.attrelid) AND (pg_attrdef.adnum = pg_attribute.attnum)
			WHERE	pg_class.oid = pg_attribute.attrelid
				AND pg_type.oid = pg_attribute.atttypid
				AND pg_attribute.attnum > 0
				AND pg_class.relname = ?";
		$statement = $this->dbObj->prepareStatement($sql);
		$statement->execute([$tableName]);
		while ($row = $statement->fetchArray()) {
			$columns[] = ['name' => $row['attname'], 'data' => [
				'type' => $row['typname'],
				'length' => $row['attlen'],
				'notNull' => $row['attnotnull'],
				'key' => (($row['contype'] == 'p') ? 'PRIMARY' : (($row['contype'] == 'u') ? 'UNIQUE' : '')),
				'default' => $row['adsrc'],
				'autoIncrement' => ($row['contype'] == 'p')
			]];
		}
		
		return $columns;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getIndices($tableName) {
		$indices = [];
		$sql = "SELECT	indexname
			FROM	pg_indexes
			WHERE	tablename = ?";
		$statement = $this->dbObj->prepareStatement($sql);
		$statement->execute([$tableName]);
		while ($row = $statement->fetchArray()) {
			$indices[] = $row['indexname'];
		}
		
		return $indices;
	}
	
	/**
	 * Returns the data of an existing database table column.
	 * 
	 * @param	string		$tableName
	 * @param	string		$columnName
	 * @return	array
	 * @throws	DatabaseException
	 */
	protected function getColumnData($tableName, $columnName) {
		$sql = "SELECT	pg_catalog.FORMAT_TYPE(atttypid, atttypmod) AS type, attnotnull AS notNull, atthasdef AS default
			FROM	pg_catalog.pg_attribute
			WHERE	attrelid = (
					SELECT	oid
					FROM	pg_catalog.pg_class
					WHERE	relname = '".$this->dbObj->escapeString($tableName)."'
						AND relnamespace = (SELECT oid FROM pg_catalog.pg_namespace WHERE nspname = 'public')
				)
				AND attname = '".$this->dbObj->escapeString($columnName)."'";
		$statement = $this->dbObj->prepareStatement($sql);
		$statement->execute();
		$row = $statement->fetchArray();
		if (empty($row['type'])) {
			throw new DatabaseException("Can not retrieve data for column '".$columnName."' on table '".$tableName."'", $this->dbObj);
		}
		
		// parse type
		if (preg_match('~(\w)\((\d+)(?:,(\d+))?\)~i', $row['type'], $match)) {
			$row['type'] = $match[1];
			$row['length'] = $match[2];
			$row['decimal'] = $match[3];
		}
		
		return $row;
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
		foreach ($indices as $index) {
			if ($index['data']['type'] == 'PRIMARY') {
				if (!empty($indexDefinition)) $indexDefinition .= ',';
				$indexDefinition .= 'PRIMARY KEY ('.$index['data']['columns'].')';
			}
		}
		
		// create table
		$sql = "CREATE TABLE ".$tableName." (
				".$columnDefinition."
				".(!empty($indexDefinition) ? ',' : '')."
				".$indexDefinition."
			)";
		$statement = $this->dbObj->prepareStatement($sql);
		$statement->execute();
		
		// create other indices
		foreach ($indices as $index) {
			if ($index['data']['type'] != 'PRIMARY') {
				$this->addIndex($tableName, $index['name'], $index['data']);
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function dropTable($tableName) {
		$sql = "DROP TABLE IF EXISTS ".$tableName." CASCADE";
		$statement = $this->dbObj->prepareStatement($sql);
		$statement->execute();
	}
	
	/**
	 * @inheritDoc
	 */
	public function addColumn($tableName, $columnName, $columnData) {
		$sql = "ALTER TABLE ".$tableName." ADD COLUMN ".$this->buildColumnDefinition($columnName, $columnData);
		$statement = $this->dbObj->prepareStatement($sql);
		$statement->execute();
	}
	
	/**
	 * @inheritDoc
	 */
	public function alterColumn($tableName, $oldColumnName, $newColumnName, $newColumnData) {
		// change column name if necessary
		if ($oldColumnName != $newColumnName) {
			$sql = "ALTER TABLE ".$tableName." RENAME COLUMN ".$oldColumnName." TO ".$newColumnName;
			$statement = $this->dbObj->prepareStatement($sql);
			$statement->execute();
		}
		
		// get column information
		$columnData = $this->getColumnData($tableName, $newColumnName);
		
		// change column type
		$alterStatements = '';
		$newColumnData['type'] = $this->getColumnType($newColumnData['type']);
		if ($columnData['type'] != $newColumnData['type'] || (isset($columnData['length']) && isset($newColumnData['length']) && $columnData['length'] != $newColumnData['length']) || (isset($columnData['decimals']) && isset($newColumnData['decimals']) && $columnData['decimals'] != $newColumnData['decimals'])) {
			if (!empty($alterStatements)) $alterStatements .= ',';
			$alterStatements .= "ALTER COLUMN ".$newColumnName." TYPE ".$this->buildColumnType($newColumnData);
		}
		
		// change not null status
		if (empty($columnData['notNull']) && !empty($newColumnData['notNull'])) {
			if (!empty($alterStatements)) $alterStatements .= ',';
			$alterStatements .= "ALTER COLUMN ".$newColumnName." SET NOT NULL";
		}
		else if (!empty($columnData['notNull']) && empty($newColumnData['notNull'])) {
			if (!empty($alterStatements)) $alterStatements .= ',';
			$alterStatements .= "ALTER COLUMN ".$newColumnName." DROP NOT NULL";
		}
		
		// change default value
		if ((isset($columnData['default']) && $columnData['default'] !== '') && (!isset($newColumnData['default']) || $newColumnData['default'] === '')) {
			if (!empty($alterStatements)) $alterStatements .= ',';
			$alterStatements .= "ALTER COLUMN ".$newColumnName." DROP DEFAULT";
		}
		else if (isset($newColumnData['default']) && $newColumnData['default'] !== '') {
			if (!empty($alterStatements)) $alterStatements .= ',';
			$alterStatements .= "ALTER COLUMN ".$newColumnName." SET DEFAULT ".$newColumnData['default'];
		}
		
		// send alter statement
		if (!empty($alterStatements)) {
			$sql = "ALTER TABLE ".$tableName." ".$alterStatements;
			$statement = $this->dbObj->prepareStatement($sql);
			$statement->execute();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function dropColumn($tableName, $columnName) {
		$sql = "ALTER TABLE ".$tableName." DROP COLUMN ".$columnName." CASCADE";
		$statement = $this->dbObj->prepareStatement($sql);
		$statement->execute();
	}
	
	/**
	 * @inheritDoc
	 */
	public function addIndex($tableName, $indexName, $indexData) {
		$columns = ArrayUtil::trim(explode(',', $indexData['columns']));
		if (empty($indexName)) {
			// create index name
			$indexName = $tableName.'_'.(!empty($columns[0]) ? $columns[0] : 'generic').'_key';
			
			// solve naming conflicts
			$indices = $this->getIndices($tableName);
			$i = 2;
			while (in_array($indexName, $indices)) {
				$indexName = $tableName.'_'.(!empty($columns[0]) ? $columns[0] : 'generic').'_'.$i.'_key';
				$i++;
			}
		}
		else if ($indexData['type'] != 'FULLTEXT') {
			$indexName = $tableName.'_'.$indexName.'_key';
		}
		
		if ($indexData['type'] == 'FULLTEXT') {
			// add new column for fulltext index
			$sql = "ALTER TABLE ".$tableName." ADD COLUMN ".$indexName." tsvector";
			$statement = $this->dbObj->prepareStatement($sql);
			$statement->execute();
			
			// add gin index
			$sql = "CREATE INDEX ".$tableName."_".$indexName."_fulltext_key ON ".$tableName." USING gin(".$indexName.")";
			$statement = $this->dbObj->prepareStatement($sql);
			$statement->execute();
			
			// update fulltext index
			$sql = "UPDATE	".$tableName."
				SET	".$indexName." = to_tsvector('english', \"".implode('" || \' \' || "', $columns)."\")";
			$statement = $this->dbObj->prepareStatement($sql);
			$statement->execute();
			
			// add trigger
			$sql = "CREATE TRIGGER		".$tableName."_".$indexName."_trigger
				BEFORE INSERT OR UPDATE
				ON			".$tableName."
				FOR EACH ROW EXECUTE PROCEDURE
				tsvector_update_trigger(".$indexName.", 'pg_catalog.english', ".implode(', ', $columns).");";
			$statement = $this->dbObj->prepareStatement($sql);
			$statement->execute();
		}
		else {
			$sql = "CREATE ".($indexData['type'] == 'UNIQUE' ? "UNIQUE " : "")."INDEX ".$indexName." ON ".$tableName." (".$indexData['columns'].")";
			$statement = $this->dbObj->prepareStatement($sql);
			$statement->execute();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function addForeignKey($tableName, $indexName, $indexData) {
		$sql = "ALTER TABLE ".$tableName." ADD";
		
		// add index name
		if (!empty($indexName)) $sql .= " CONSTRAINT ".$indexName;
		
		// add columns
		$sql .= " FOREIGN KEY (".str_replace(',', ',', preg_replace('/\s+/', '', $indexData['columns'])).")";
		
		// add referenced table name
		$sql .= " REFERENCES ".$indexData['referencedTable'];
		
		// add referenced columns
		$sql .= " (".str_replace(',', ',', preg_replace('/\s+/', '', $indexData['referencedColumns'])).")";
		
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
		$sql = "DROP INDEX IF EXISTS ".$tableName."_".$indexName."_key CASCADE";
		$statement = $this->dbObj->prepareStatement($sql);
		$statement->execute();
	}
	
	/**
	 * @inheritDoc
	 */
	public function dropForeignKey($tableName, $indexName) {
		// TODO: Could it be, that this method is not required because Postgre is clever enough to delete references anyway?
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
		$definition = $columnName;
		// auto_increment
		if (!empty($columnData['autoIncrement'])) {
			if ($columnData['type'] == 'bigint') $definition .= " BIGSERIAL";
			else $definition .= " SERIAL";
			// key
			if (!empty($columnData['key'])) $definition .= ' '.$columnData['key'].($columnData['key'] == 'PRIMARY' ? ' KEY' : '');
			
			return $definition;
		}
		
		// column type
		if ($columnData['type'] == 'enum') $columnData['length'] = 255;
		$columnData['type'] = $this->getColumnType($columnData['type']);
		$definition .= " ".$this->buildColumnType($columnData);
		
		// not null / null
		if (!empty($columnData['notNull'])) $definition .= " NOT NULL";
		// default
		if (isset($columnData['default']) && $columnData['default'] !== '') $definition .= " DEFAULT ".$columnData['default'];
		// key
		if (!empty($columnData['key'])) $definition .= ' '.$columnData['key'].($columnData['key'] == 'PRIMARY' ? ' KEY' : '');
		
		return $definition;
	}
	
	/**
	 * Builds a column type for execution in a create table or alter table statement.
	 * 
	 * @param	array		$columnData
	 * @return	string
	 */
	protected function buildColumnType($columnData) {
		$definition = strtoupper($columnData['type']);
		
		// column length and decimals
		if (!empty($columnData['length'])) {
			if (!empty($columnData['decimals']) && $columnData['type'] == 'numeric') {
				$definition .= "(".$columnData['length'].",".$columnData['decimals'].")";
			}
			else if ($columnData['type'] == 'character' || $columnData['type'] == 'character varying') {
				$definition .= "(".$columnData['length'].")";
			}
		}
		
		return $definition;
	}
	
	/**
	 * Converts a MySQL column type to the matching PostgreSQL column type.
	 * 
	 * @param	string		$mySQLType
	 * @return	string
	 * @throws	DatabaseException
	 */
	protected function getColumnType($mySQLType) {
		switch ($mySQLType) {
			// numeric types
			case 'tinyint':
			case 'smallint':
				return 'smallint';
			
			case 'mediumint':
			case 'int':
				return 'integer';
			
			case 'bigint':
				return 'bigint';
			
			case 'float':
				return 'real';
			
			case 'double':
				return 'double precision';
			
			case 'decimal':
			case 'numeric':
				return 'numeric';
			
			// string types
			case 'char':
				return 'character';
			
			case 'varchar':
				return 'character varying';
			
			case 'tinytext':
			case 'text':
			case 'mediumtext':
			case 'longtext':
				return 'text';
			
			// blobs
			case 'binary':
			case 'varbinary':
			case 'tinyblob':
			case 'blob':
			case 'mediumblob':
			case 'longblob':
				return 'bytea';
			
			// enum
			case 'enum':
				return 'character varying';
		}
		
		throw new DatabaseException("Unknown / unsupported data type '".$mySQLType."'", $this->dbObj);
	}
}
