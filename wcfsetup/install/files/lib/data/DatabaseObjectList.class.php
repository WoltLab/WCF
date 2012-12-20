<?php
namespace wcf\data;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Abstract class for a list of database objects.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data
 * @category	Community Framework
 */
abstract class DatabaseObjectList implements \Countable, ITraversableObject {
	/**
	 * class name
	 * @var	string
	 */
	public $className = '';
	
	/**
	 * object class name
	 * @var	string
	 */
	public $objectClassName = '';
	
	/**
	 * result objects
	 * @var	array<wcf\data\DatabaseObject>
	 */
	public $objects = array();
	
	/**
	 * ids of result objects
	 * @var	array<integer>
	 */
	public $objectIDs = null;
	
	/**
	 * sql offset
	 * @var	integer
	 */
	public $sqlOffset = 0;
	
	/**
	 * sql limit
	 * @var	integer
	 */
	public $sqlLimit = 20;
	
	/**
	 * sql order by statement
	 * @var	string
	 */
	public $sqlOrderBy = '';
	
	/**
	 * sql select parameters
	 * @var	string
	 */
	public $sqlSelects = '';
	
	/**
	 * sql select joins which are necessary for where statements
	 * @var	string
	 */
	public $sqlConditionJoins = '';
	
	/**
	 * sql select joins
	 * @var	string
	 */
	public $sqlJoins = '';
	
	/**
	 * enables the automatic usage of the qualified shorthand 
	 * @var	boolean
	 */
	public $useQualifiedShorthand = true;
	
	/**
	 * sql conditions
	 * @var	wcf\system\database\util\PreparedStatementConditionBuilder
	 */
	protected $conditionBuilder = null;
	
	/**
	 * current iterator index
	 * @var	integer
	 */
	protected $index = 0;
	
	/**
	 * list of index to object relation
	 * @var	array<integer>
	 */
	protected $indexToObject = null;
	
	/**
	 * Creates a new DatabaseObjectList object.
	 */
	public function __construct() {
		// set class name
		if (empty($this->className)) {
			$className = get_called_class();
			
			if (StringUtil::substring($className, -4) == 'List') {
				$this->className = StringUtil::substring($className, 0, -4);
			}
		}
		
		$this->conditionBuilder = new PreparedStatementConditionBuilder();
	}
	
	/**
	 * Counts the number of objects.
	 * 
	 * @return	integer
	 */
	public function countObjects() {
		$sql = "SELECT	COUNT(*) AS count
			FROM	".$this->getDatabaseTableName()." ".$this->getDatabaseTableAlias()."
			".$this->sqlConditionJoins."
			".$this->getConditionBuilder()->__toString();
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($this->getConditionBuilder()->getParameters());
		$row = $statement->fetchArray();
		return $row['count'];
	}
	
	/**
	 * Reads the object ids from database.
	 */
	public function readObjectIDs() {
		$this->objectIDs = array();
		$sql = "SELECT	".$this->getDatabaseTableAlias().".".$this->getDatabaseTableIndexName()." AS objectID
			FROM	".$this->getDatabaseTableName()." ".$this->getDatabaseTableAlias()."
				".$this->sqlConditionJoins."
				".$this->getConditionBuilder()->__toString()."
				".(!empty($this->sqlOrderBy) ? "ORDER BY ".$this->sqlOrderBy : '');
		$statement = WCF::getDB()->prepareStatement($sql, $this->sqlLimit, $this->sqlOffset);
		$statement->execute($this->getConditionBuilder()->getParameters());
		while ($row = $statement->fetchArray()) {
			$this->objectIDs[] = $row['objectID'];
		}
	}
	
	/**
	 * Reads the objects from database.
	 */
	public function readObjects() {
		if ($this->objectIDs !== null) {
			if (empty($this->objectIDs)) {
				return;
			}
			$sql = "SELECT	".(!empty($this->sqlSelects) ? $this->sqlSelects.($this->useQualifiedShorthand ? ',' : '') : '')."
					".($this->useQualifiedShorthand ? $this->getDatabaseTableAlias().'.*' : '')."
				FROM	".$this->getDatabaseTableName()." ".$this->getDatabaseTableAlias()."
					".$this->sqlJoins."
				WHERE	".$this->getDatabaseTableAlias().".".$this->getDatabaseTableIndexName()." IN (?".str_repeat(',?', count($this->objectIDs) - 1).")
					".(!empty($this->sqlOrderBy) ? "ORDER BY ".$this->sqlOrderBy : '');
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($this->objectIDs);
			$this->objects = $statement->fetchObjects(($this->objectClassName ?: $this->className));
		}
		else {
			$sql = "SELECT	".(!empty($this->sqlSelects) ? $this->sqlSelects.($this->useQualifiedShorthand ? ',' : '') : '')."
					".($this->useQualifiedShorthand ? $this->getDatabaseTableAlias().'.*' : '')."
				FROM	".$this->getDatabaseTableName()." ".$this->getDatabaseTableAlias()."
					".$this->sqlJoins."
					".$this->getConditionBuilder()->__toString()."
					".(!empty($this->sqlOrderBy) ? "ORDER BY ".$this->sqlOrderBy : '');
			$statement = WCF::getDB()->prepareStatement($sql, $this->sqlLimit, $this->sqlOffset);
			$statement->execute($this->getConditionBuilder()->getParameters());
			$this->objects = $statement->fetchObjects(($this->objectClassName ?: $this->className));
		}
		
		// use table index as array index
		$objects = array();
		foreach ($this->objects as $object) {
			$objectID = $object->{$this->getDatabaseTableIndexName()};
			$objects[$objectID] = $object;
			
			$this->indexToObject[] = $objectID;
		}
		$this->objectIDs = $this->indexToObject;
		$this->objects = $objects;
	}
	
	/**
	 * Returns the object ids of the list.
	 * 
	 * @return	array<integer>
	 */
	public function getObjectIDs() {
		return $this->objectIDs;
	}
	
	/**
	 * Sets the object ids.
	 * 
	 * @param	array<integer>		$objectIDs
	 */
	public function setObjectIDs(array $objectIDs) {
		$this->objectIDs = $objectIDs;
	}
	
	/**
	 * Returns the objects of the list.
	 * 
	 * @return	array<wcf\data\DatabaseObject>
	 */
	public function getObjects() {
		return $this->objects;
	}
	
	/**
	 * Returns the condition builder object.
	 * 
	 * @return	wcf\system\database\util\PreparedStatementConditionBuilder
	 */
	public function getConditionBuilder() {
		return $this->conditionBuilder;
	}
	
	/**
	 * Returns the name of the database table.
	 * 
	 * @return	string
	 */
	public function getDatabaseTableName() {
		return call_user_func(array($this->className, 'getDatabaseTableName'));
	}
	
	/**
	 * Returns the name of the database table.
	 * 
	 * @return	string
	 */
	public function getDatabaseTableIndexName() {
		return call_user_func(array($this->className, 'getDatabaseTableIndexName'));
	}
	
	/**
	 * Returns the name of the database table alias.
	 * 
	 * @return	string
	 */
	public function getDatabaseTableAlias() {
		return call_user_func(array($this->className, 'getDatabaseTableAlias'));
	}
	
	/**
	 * @see	\Countable::count()
	 */
	public function count() {
		return count($this->objects);
	}
	
	/**
	 * @see	\Iterator::current()
	 */
	public function current() {
		$objectID = $this->indexToObject[$this->index];
		return $this->objects[$objectID];
	}
	
	/**
	 * CAUTION: This methods does not return the current iterator index,
	 * rather than the object key which maps to that index.
	 * 
	 * @see	\Iterator::key()
	 */
	public function key() {
		return $this->indexToObject[$this->index];
	}
	
	/**
	 * @see	\Iterator::next()
	 */
	public function next() {
		++$this->index;
	}
	
	/**
	 * @see	\Iterator::rewind()
	 */
	public function rewind() {
		$this->index = 0;
	}
	
	/**
	 * @see	\Iterator::valid()
	 */
	public function valid() {
		return isset($this->indexToObject[$this->index]);
	}
	
	/**
	 * @see	\SeekableIterator::seek()
	 */
	public function seek($index) {
		$this->index = $index;
		
		if (!$this->valid()) {
			throw new \OutOfBoundsException();
		}
	}
	
	/**
	 * @see	wcf\data\ITraversableObject::seekTo()
	 */
	public function seekTo($objectID) {
		$this->index = array_search($objectID, $this->indexToObject);
		
		if ($this->index === false) {
			throw new SystemException("object id '".$objectID."' is invalid");
		}
	}
	
	/**
	 * @see	wcf\data\ITraversableObject::search()
	 */
	public function search($objectID) {
		try {
			$this->seekTo($objectID);
			return $this->current();
		}
		catch (SystemException $e) {
			return null;
		}
	}
}
