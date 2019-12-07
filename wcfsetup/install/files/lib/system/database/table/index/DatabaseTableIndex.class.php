<?php
namespace wcf\system\database\table\index;
use wcf\system\database\table\TDroppableDatabaseComponent;

/**
 * Represents an index of a database table.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Database\Table\Index
 * @since	5.2
 */
class DatabaseTableIndex {
	use TDroppableDatabaseComponent;
	
	/**
	 * indexed columns
	 * @var	string[]
	 */
	protected $columns;
	
	/**
	 * is `true` if index name has been automatically generated
	 * @var	bool
	 */
	protected $generatedName = false;
	
	/**
	 * name of index
	 * @var	string
	 */
	protected $name;
	
	/**
	 * type of index (see `*_TYPE` constants)
	 * @var	null|string
	 */
	protected $type;
	
	const DEFAULT_TYPE = null;
	const PRIMARY_TYPE = 'PRIMARY';
	const UNIQUE_TYPE = 'UNIQUE';
	const FULLTEXT_TYPE = 'FULLTEXT';
	
	/**
	 * Creates a new `DatabaseTableIndex` object.
	 * 
	 * @param	string		$name		column name
	 */
	protected function __construct($name) {
		$this->name = $name;
	}
	
	/**
	 * Sets the indexed columns and returns the index.
	 * 
	 * @param	string[]	$columns	indexed columns
	 * @return	$this				this index
	 */
	public function columns($columns) {
		$this->columns = array_values($columns);
		
		return $this;
	}

	/**
	 * Sets the automatically generated name of the index.
	 * 
	 * @param	string		$name		index name
	 * @return	$this				this index
	 */
	public function generatedName($name) {
		$this->name($name);
		$this->generatedName = true;
		
		return $this;
	}
	
	/**
	 * Returns the index columns.
	 * 
	 * @return	string[]
	 */
	public function getColumns() {
		return $this->columns;
	}
	
	/**
	 * Returns the data used by `DatabaseEditor` to add the index to a table.
	 * 
	 * @return	array
	 */
	public function getData() {
		return [
			'columns' => implode(',', $this->columns),
			'type' => $this->type
		];
	}
	
	/**
	 * Returns the name of the index.
	 * 
	 * @return	string
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * Returns the type of the index (see `*_TYPE` constants).
	 * 
	 * @return	null|string
	 */
	public function getType() {
		return $this->type;
	}
	
	/**
	 * Returns `true` if the name of the index has been automatically generated.
	 * 
	 * @return	bool
	 */
	public function hasGeneratedName() {
		return $this->generatedName;
	}
	
	/**
	 * Sets the name of the index.
	 * 
	 * @param	string		$name		index name
	 * @return	$this				this index
	 */
	public function name($name) {
		$this->name = $name;
		
		return $this;
	}
	
	/**
	 * Sets the type of the index and returns the index
	 * 
	 * @param	null|string	$type		index type
	 * @return	$this				this index
	 * @throws	\InvalidArgumentException	if given type is invalid
	 */
	public function type($type) {
		if ($type !== static::DEFAULT_TYPE && $type !== static::PRIMARY_TYPE && $type !== static::UNIQUE_TYPE && $type !== static::FULLTEXT_TYPE) {
			throw new \InvalidArgumentException("Unknown index type '{$type}'.");
		}
		
		$this->type = $type;
		
		return $this;
	}
	
	/**
	 * Returns a `DatabaseTableIndex` object with the given name.
	 * 
	 * @param	string		$name
	 * @return	static
	 */
	public static function create($name = '') {
		return new static($name);
	}
	
	/**
	 * Returns a `DatabaseTableIndex` object with the given name and data.
	 * 
	 * @param	string		$name
	 * @param	array		$data		data returned by `DatabaseEditor::getIndexInformation()`
	 * @return	static
	 */
	public static function createFromData($name, array $data) {
		return static::create($name)
			->type($data['type'])
			->columns($data['columns']);
	}
}
