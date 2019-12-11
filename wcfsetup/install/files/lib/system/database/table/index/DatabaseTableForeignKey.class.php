<?php
namespace wcf\system\database\table\index;
use wcf\system\application\ApplicationHandler;
use wcf\system\database\table\TDroppableDatabaseComponent;

/**
 * Represents a foreign key of a database table.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Database\Table\Index
 * @since	5.2
 */
class DatabaseTableForeignKey {
	use TDroppableDatabaseComponent;
	
	/**
	 * columns affected by the foreign key 
	 * @var	string[]
	 */
	protected $columns;
	
	/**
	 * name of the foreign key
	 * @var	string
	 */
	protected $name;
	
	/**
	 * action executed in referenced table if row is deleted
	 * @var	null|string
	 */
	protected $onDelete;
	
	/**
	 * action executed in referenced table if row is updated
	 * @var	null|string
	 */
	protected $onUpdate;
	
	/**
	 * relevant columns in referenced table
	 * @var	string[]
	 */
	protected $referencedColumns;
	
	/**
	 * name of referenced table
	 * @var	string
	 */
	protected $referencedTable;
	
	/**
	 * valid on delete/update actions
	 * @var	string[]
	 */
	const VALID_ACTIONS = [
		'CASCADE',
		'NO ACTION',
		'SET NULL'
	];
	
	/**
	 * Creates a new `DatabaseTableForeignKey` object.
	 *
	 * @param	string		$name		column name
	 */
	protected function __construct($name) {
		$this->name = $name;
	}
	
	/**
	 * Sets the columns affected by the foreign key and returns the foreign key.
	 * 
	 * @param	string[]	$columns	columns affected by foreign key
	 * @return	$this				this foreign key
	 */
	public function columns(array $columns) {
		$this->columns = array_values($columns);
		
		return $this;
	}
	
	/**
	 * Returns the name of the foreign key.
	 * 
	 * If the key belongs to a database table layout not created from an existing database table,
	 * the name might be empty.
	 * 
	 * @return	string
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * Returns the columns affected by the foreign key
	 * 
	 * @return	string[]			columns affected by foreign key
	 * @throws	\BadMethodCallException		if not columns have been set
	 */
	public function getColumns() {
		if ($this->columns === null) {
			throw new \BadMethodCallException("Before getting the columns, they must be set for foreign key '{$this->getName()}'.");
		}
		
		return $this->columns;
	}
	
	/**
	 * Returns the data used by `DatabaseEditor` to add the foreign key to a table.
	 * 
	 * @return	array
	 */
	public function getData() {
		return [
			'columns' => implode(',', $this->getColumns()),
			'ON DELETE' => $this->normalizeAction($this->getOnDelete()),
			'ON UPDATE' => $this->normalizeAction($this->getOnUpdate()),
			'referencedColumns' => implode(',', $this->getReferencedColumns()),
			'referencedTable' => $this->getReferencedTable()
		];
	}
	
	/**
	 * Returns the action executed in referenced table if row is deleted or `null` if no such
	 * action has been set.
	 * 
	 * @return	null|string
	 */
	public function getOnDelete() {
		return $this->onDelete;
	}
	
	/**
	 * Returns the action executed in referenced table if row is updated or `null` if no such
	 * action has been set.
	 * 
	 * @return	null|string
	 */
	public function getOnUpdate() {
		return $this->onUpdate;
	}
	
	/**
	 * Returns the relevant columns in referenced table.
	 * 
	 * @return	string[]
	 * @throws	\BadMethodCallException		if referenced columns have not been set
	 */
	public function getReferencedColumns() {
		if ($this->referencedColumns === null) {
			throw new \BadMethodCallException("Before getting the referenced columns, they must be set for foreign key '{$this->getName()}'.");
		}
		
		return $this->referencedColumns;
	}
	
	/**
	 * Returns the name of the referenced table.
	 * 
	 * @return	string
	 * @throws	\BadMethodCallException		if referenced table has not been set
	 */
	public function getReferencedTable() {
		if ($this->referencedTable === null) {
			throw new \BadMethodCallException("Before getting the referenced table, it must be set for foreign key '{$this->getName()}'.");
		}
		
		return $this->referencedTable;
	}
	
	/**
	 * Sets the name of the foreign key.
	 *
	 * @param	string		$name		index name
	 * @return	$this				this index
	 */
	public function name($name) {
		$this->name = $name;
		
		return $this;
	}
	
	/**
	 * Sets the action executed in referenced table if row is deleted and returns the foreign
	 * key.
	 * 
	 * @param	string		$onDelete	action executed in referenced table if row is deleted
	 * @return	$this				this foreign key
	 * @throws	\InvalidArgumentException	if given action is invalid
	 */
	public function onDelete($onDelete) {
		if ($onDelete !== null && !in_array($onDelete, static::VALID_ACTIONS)) {
			throw new \InvalidArgumentException("Unknown on delete action '{$onDelete}'.");
		}
		
		$this->onDelete = $onDelete;
		
		return $this;
	}
	
	/**
	 * Sets the action executed in referenced table if row is updated and returns the foreign
	 * key.
	 * 
	 * @param	string		$onUpdate	action executed in referenced table if row is updated
	 * @return	$this				this foreign key
	 * @throws	\InvalidArgumentException	if given action is invalid
	 */
	public function onUpdate($onUpdate) {
		if ($onUpdate !== null && !in_array($onUpdate, static::VALID_ACTIONS)) {
			throw new \InvalidArgumentException("Unknown on update action '{$onUpdate}'.");
		}
		
		$this->onUpdate = $onUpdate;
		
		return $this;
	}
	
	/**
	 * Sets the relevant columns of the referenced table and returns the foreign key.
	 * 
	 * @param	string[]	$referencedColumns	columns of referenced table
	 * @return	$this					this foreign key
	 */
	public function referencedColumns(array $referencedColumns) {
		$this->referencedColumns = $referencedColumns;
		
		return $this;
	}
	
	/**
	 * Sets the name of the referenced table and returns the foreign key.
	 *
	 * @param	string		$referencedTable	name of referenced table
	 * @return	$this					this foreign key
	 */
	public function referencedTable($referencedTable) {
		$this->referencedTable = ApplicationHandler::insertRealDatabaseTableNames($referencedTable);
		
		return $this;
	}
	
	/**
	 * In MySQL, `ON * RESTRICT`, `ON * NO ACTION` or omitting it entirely, is completely the same. However,
	 * MySQL 8 reports `NO ACTION` where MySQL 5.7 would identify the action as `null`. This method normalized
	 * the action, by always setting it to null if the value is `RESTRICT` or `NO ACTION`.
	 * 
	 * @param string $action
	 * @return string|null
	 */
	protected function normalizeAction($action) {
		if ($action === 'RESTRICT' || $action === 'NO ACTION') {
			return null;
		}
		
		return $action;
	}
	
	/**
	 * Returns a `DatabaseTableForeignKey` object with the given name.
	 * 
	 * @param	string		$name
	 * @return	static
	 */
	public static function create($name = '') {
		return new static($name);
	}
	
	/**
	 * Returns a `DatabaseTableForeignKey` object with the given name and data.
	 * 
	 * @param	string		$name
	 * @param	array		$data		data returned by `DatabaseEditor::getForeignKeys()`
	 * @return	static
	 */
	public static function createFromData($name, $data) {
		return static::create($name)
				->columns($data['columns'])
				->onDelete($data['ON DELETE'])
				->onUpdate($data['ON UPDATE'])
				->referencedColumns($data['referencedColumns'])
				->referencedTable($data['referencedTable']);
	}
}
