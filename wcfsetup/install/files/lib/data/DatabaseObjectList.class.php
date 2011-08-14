<?php
namespace wcf\data;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Abstract class for a list of database objects.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data
 * @category 	Community Framework
 */
abstract class DatabaseObjectList {
	/**
	 * object class name
	 * 
	 * @var	string
	 */
	public $className = '';
	
	/**
	 * result objects
	 * 
	 * @var	array<wcf\data\DatabaseObject>
	 */
	public $objects = array();
	
	/**
	 * result object ids
	 * 
	 * @var	array<integer>
	 */
	public $objectIDs = null; 
	
	/**
	 * sql offset
	 *
	 * @var integer
	 */
	public $sqlOffset = 0;
	
	/**
	 * sql limit
	 *
	 * @var integer
	 */
	public $sqlLimit = 20;
	
	/**
	 * sql order by statement
	 *
	 * @var	string
	 */
	public $sqlOrderBy = '';
	
	/**
	 * sql select parameters
	 *
	 * @var string
	 */
	public $sqlSelects = '';
	
	/**
	 * sql select joins which are necessary for where statements
	 *
	 * @var string
	 */
	public $sqlConditionJoins = '';
	
	/**
	 * sql select joins
	 *
	 * @var string
	 */
	public $sqlJoins = '';
	
	/**
	 * sql conditions
	 *
	 * @var wcf\system\database\util\PreparedStatementConditionBuilder
	 */
	protected $conditionBuilder = null;
	
	/**
	 * Creates a new DatabaseObjectList object.
	 */
	public function __construct() {
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
			if (!count($this->objectIDs)) {
				return;
			}
			
			$sql = "SELECT	".(!empty($this->sqlSelects) ? $this->sqlSelects.',' : '')."
					".$this->getDatabaseTableAlias().".*
				FROM	".$this->getDatabaseTableName()." ".$this->getDatabaseTableAlias()."
					".$this->sqlJoins."
				WHERE	".$this->getDatabaseTableAlias().".".$this->getDatabaseTableIndexName()." IN (?".str_repeat(',?', count($this->objectIDs) - 1).")
					".(!empty($this->sqlOrderBy) ? "ORDER BY ".$this->sqlOrderBy : '');
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($this->objectIDs);
			$this->objects = $statement->fetchObjects($this->className);
		}
		else {
			$sql = "SELECT	".(!empty($this->sqlSelects) ? $this->sqlSelects.',' : '')."
					".$this->getDatabaseTableAlias().".*
				FROM	".$this->getDatabaseTableName()." ".$this->getDatabaseTableAlias()."
					".$this->sqlJoins."
					".$this->getConditionBuilder()->__toString()."
					".(!empty($this->sqlOrderBy) ? "ORDER BY ".$this->sqlOrderBy : '');
			$statement = WCF::getDB()->prepareStatement($sql, $this->sqlLimit, $this->sqlOffset);
			$statement->execute($this->getConditionBuilder()->getParameters());
			$this->objects = $statement->fetchObjects($this->className);
		}
		
		// use table index as array index
		$objects = array();
		foreach($this->objects as $object) {
			$objects[$object->{$this->getDatabaseTableIndexName()}] = $object;
		}
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
	 * @return string
	 */
	public function getDatabaseTableName() {
		return call_user_func(array($this->className, 'getDatabaseTableName'));
	}
	
	/**
	 * Returns the name of the database table.
	 * 
	 * @return string
	 */
	public function getDatabaseTableIndexName() {
		return call_user_func(array($this->className, 'getDatabaseTableIndexName'));
	}
	
	/**
	 * Returns the name of the database table alias.
	 * 
	 * @return string
	 */
	public function getDatabaseTableAlias() {
		return call_user_func(array($this->className, 'getDatabaseTableAlias'));
	}
}
