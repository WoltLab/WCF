<?php
namespace wcf\system\database\table;
use wcf\system\application\ApplicationHandler;
use wcf\system\database\editor\DatabaseEditor;
use wcf\system\database\table\column\IDatabaseTableColumn;
use wcf\system\database\table\index\DatabaseTableForeignKey;
use wcf\system\database\table\index\DatabaseTableIndex;

/**
 * PHP representation of an existing database table or the intended layout of an non-existing or
 * existing database table.
 * 
 * @author	Alexander Ebert, Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Database\Table
 * @since	5.2
 */
class DatabaseTable {
	use TDroppableDatabaseComponent;
	
	/**
	 * intended database table's columns
	 * @var	IDatabaseTableColumn[]
	 */
	protected $columns = [];
	
	/**
	 * intended database table's foreign keys
	 * @var	DatabaseTableForeignKey[]
	 */
	protected $foreignKeys = [];
	
	/**
	 * intended database table's indices
	 * @var	DatabaseTableIndex[]
	 */
	protected $indices = [];
	
	/**
	 * name of the database table
	 * @var	string
	 */
	protected $name;
	
	/**
	 * Creates a new instance of `DatabaseTable`.
	 * 
	 * @param	string		$name	name of the database table
	 */
	protected function __construct($name) {
		$this->name = ApplicationHandler::insertRealDatabaseTableNames($name);
	}
	
	/**
	 * Sets the columns of the database table.
	 * 
	 * @param	IDatabaseTableColumn[]	$columns	added/dropped columns
	 * @return	$this					this database table
	 * @throws	\InvalidArgumentException		if any column is invalid or duplicate column names exist
	 */
	public function columns(array $columns) {
		$this->columns = [];
		foreach ($columns as $column) {
			if (!($column instanceof IDatabaseTableColumn)) {
				throw new \InvalidArgumentException("Added columns have to be instances of '" . IDatabaseTableColumn::class . "'.");
			}
			
			if (isset($this->columns[$column->getName()])) {
				throw new \InvalidArgumentException("Duplicate column with name '{$column->getName()}'.");
			}
			
			$this->columns[$column->getName()] = $column;
		}
		
		return $this;
	}
	
	/**
	 * Sets the foreign keys of the database table.
	 * 
	 * @param	DatabaseTableForeignKey[]	$foreignKeys	added/dropped foreign keys
	 * @return	$this						this database table
	 * @throws	\InvalidArgumentException			if any foreign key is invalid or duplicate foreign key names exist
	 */
	public function foreignKeys(array $foreignKeys) {
		$this->foreignKeys = [];
		foreach ($foreignKeys as $foreignKey) {
			if (!($foreignKey instanceof DatabaseTableForeignKey)) {
				throw new \InvalidArgumentException("Added foreign keys have to be instances of '" . DatabaseTableForeignKey::class . "'.");
			}
			
			if (empty($foreignKey->getColumns())) {
				throw new \InvalidArgumentException("Missing columns for foreign key.");
			}
			
			if ($foreignKey->getName() === '') {
				$foreignKey->name(md5($this->getName() . '_' . $foreignKey->getColumns()[0]) . '_fk');
			}
			
			if (isset($this->foreignKeys[$foreignKey->getName()])) {
				throw new \InvalidArgumentException("Duplicate foreign key with name '{$foreignKey->getName()}'.");
			}
			
			$this->foreignKeys[$foreignKey->getName()] = $foreignKey;
		}
		
		return $this;
	}
	
	/**
	 * Returns the columns of the table.
	 * 
	 * @return	IDatabaseTableColumn[]
	 */
	public function getColumns() {
		return $this->columns;
	}
	
	/**
	 * Returns the foreign keys of the table.
	 *
	 * @return	DatabaseTableForeignKey[]
	 */
	public function getForeignKeys() {
		return $this->foreignKeys;
	}
	
	/**
	 * Returns the indices of the table.
	 * 
	 * @return	DatabaseTableIndex[]
	 */
	public function getIndices() {
		return $this->indices;
	}
	
	/**
	 * Returns the name of the database table.
	 * 
	 * @return	string		database table name
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * Returns a `DatabaseTable` object with the given name.
	 *
	 * @param	string		$tableName
	 * @return	static
	 */
	public static function create($tableName) {
		return new static($tableName);
	}
	
	/**
	 * Returns a `DatabaseTable` object for an existing database table with the given name.
	 * 
	 * @param	DatabaseEditor		$dbEditor
	 * @param	string			$tableName
	 * @return	DatabaseTable
	 */
	public static function createFromExistingTable(DatabaseEditor $dbEditor, $tableName) {
		$table = new static($tableName);
		
		$columns = [];
		foreach ($dbEditor->getColumns($tableName) as $columnData) {
			$className = 'wcf\system\database\table\column\\' . ucfirst(strtolower($columnData['data']['type'])) . 'DatabaseTableColumn';
			if (!class_exists($className)) {
				throw new \InvalidArgumentException("Unknown database table column type '{$columnData['data']['type']}'.");
			}
			
			$columns[$columnData['name']] = $className::createFromData($columnData['name'], $columnData['data']);
		}
		$table->columns($columns);
		
		$foreignKeys = [];
		foreach ($dbEditor->getForeignKeys($tableName) as $foreignKeysName => $foreignKeyData) {
			$foreignKeys[$foreignKeysName] = DatabaseTableForeignKey::createFromData($foreignKeysName, $foreignKeyData);
		}
		$table->foreignKeys($foreignKeys);
		
		$indices = [];
		foreach ($dbEditor->getIndexInformation($tableName) as $indexName => $indexData) {
			if (!isset($foreignKeys[$indexName])) {
				$indices[$indexName] = DatabaseTableIndex::createFromData($indexName, $indexData);
			}
		}
		$table->indices($indices);
		
		return $table;
	}
	
	/**
	 * Sets the indices of the database table.
	 * 
	 * @param	DatabaseTableIndex[]	$indices	added/dropped indices
	 * @return	$this					this database table
	 * @throws	\InvalidArgumentException		if any index is invalid or duplicate index key names exist
	 */
	public function indices(array $indices) {
		$this->indices = [];
		foreach ($indices as $index) {
			if (!($index instanceof DatabaseTableIndex)) {
				throw new \InvalidArgumentException("Added indices have to be instances of '" . DatabaseTableIndex::class . "'.");
			}
			
			if ($index->getName() === '') {
				$index->name(md5($this->getName() . '_' . $index->getColumns()[0]));
			}
			
			if (isset($this->foreignKeys[$index->getName()])) {
				throw new \InvalidArgumentException("Duplicate index with name '{$index->getName()}'.");
			}
			
			$this->indices[$index->getName()] = $index;
		}
		
		return $this;
	}
}
