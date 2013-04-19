<?php
namespace wcf\data;
use wcf\system\WCF;

/**
 * Abstract class for all data holder classes.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data
 * @category	Community Framework
 */
abstract class DatabaseObject implements IStorableObject {
	/**
	 * database table for this object
	 * @var	string
	 */
	protected static $databaseTableName = '';
	
	/**
	 * indicates if database table index is an identity column
	 * @var	boolean 
	 */
	protected static $databaseTableIndexIsIdentity = true;
	
	/**
	 * name of the primary index column
	 * @var	string
	 */
	protected static $databaseTableIndexName = '';
	
	/**
	 * sort field
	 * @var	mixed
	 */
	protected static $sortBy = null;
	
	/**
	 * sort order
	 * @var	mixed
	 */
	protected static $sortOrder = null;
	
	/**
	 * object data
	 * @var	array
	 */
	protected $data = null;
	
	/**
	 * Creates a new instance of the DatabaseObject class.
	 * 
	 * @param	mixed				$id
	 * @param	array				$row
	 * @param	wcf\data\DatabaseObject		$object
	 */
	public function __construct($id, array $row = null, DatabaseObject $object = null) {
		if ($id !== null) {
			$sql = "SELECT	*
				FROM	".static::getDatabaseTableName()."
				WHERE	".static::getDatabaseTableIndexName()." = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array($id));
			$row = $statement->fetchArray();
			
			// enforce data type 'array'
			if ($row === false) $row = array();
		}
		else if ($object !== null) {
			$row = $object->data;
		}
		
		$this->handleData($row);
	}
	
	/**
	 * Stores the data of a database row.
	 * 
	 * @param	array		$data
	 */
	protected function handleData($data) {
		// provide a logical false value for - assumed numeric - primary index
		if (!isset($data[static::getDatabaseTableIndexName()])) {
			$data[static::getDatabaseTableIndexName()] = 0;
		}
		
		$this->data = $data;
	}
	
	/**
	 * @see	wcf\data\IStorableObject::__get()
	 */
	public function __get($name) {
		if (isset($this->data[$name])) {
			return $this->data[$name];
		}
		else {
			return null;
		}
	}
	
	/**
	 * Returns the id of the object.
	 * 
	 * @return	mixed
	 */
	public function getObjectID() {
		return $this->data[static::getDatabaseTableIndexName()];
	}
	
	/**
	 * @see	wcf\data\IStorableObject::__isset()
	 */
	public function __isset($name) {
		return isset($this->data[$name]);
	}
	
	/**
	 * @see	wcf\data\IStorableObject::getData()
	 */
	public function getData() {
		return $this->data;
	}
	
	/**
	 * @see	wcf\data\IStorableObject::getDatabaseTableName()
	 */
	public static function getDatabaseTableName() {
		return 'wcf'.WCF_N.'_'.static::$databaseTableName;
	}
	
	/**
	 * @see	wcf\data\IStorableObject::getDatabaseTableAlias()
	 */
	public static function getDatabaseTableAlias() {
		return static::$databaseTableName;
	}
	
	/**
	 * @see	wcf\data\IStorableObject::getDatabaseTableIndexIsIdentity()
	 */
	public static function getDatabaseTableIndexIsIdentity() {
		return static::$databaseTableIndexIsIdentity;
	}
	
	/**
	 * @see	wcf\data\IStorableObject::getDatabaseTableIndexName()
	 */
	public static function getDatabaseTableIndexName() {
		return static::$databaseTableIndexName;
	}
	
	/**
	 * Sorts a list of database objects.
	 * 
	 * @param	array<wcf\data\DatabaseObject>	$objects
	 * @param	mixed				$sortBy
	 * @param	string				$sortOrder
	 * @return	boolean
	 */
	public static function sort(&$objects, $sortBy, $sortOrder = 'ASC', $maintainIndexAssociation = true) {
		$sortArray = $objects2 = array();
		foreach ($objects as $idx => $obj) {
			$sortArray[$idx] = $obj->$sortBy;
			
			// array_multisort will drop index association if key is not a string
			if ($maintainIndexAssociation) {
				$objects2[$idx.'x'] = $obj;
			}
		}
		
		if ($maintainIndexAssociation) {
			$objects = array();
			array_multisort($sortArray, $sortOrder == 'ASC' ? SORT_ASC : SORT_DESC, $objects2);
			
			$objects = array();
			foreach ($objects2 as $idx => $obj) {
				$objects[substr($idx, 0, -1)] = $obj;
			}
		}
		else {
			array_multisort($sortArray, $sortOrder == 'ASC' ? SORT_ASC : SORT_DESC, $objects);
		}
	}
	
	/**
	 * Compares two database objects.
	 * 
	 * @param	wcf\data\DatabaseObject	$objectA
	 * @param	wcf\data\DatabaseObject	$objectB
	 * @return	boolean
	 */
	public static function compare($objectA, $objectB) {
		if (get_class($objectA) != get_class($objectB)) return false;
		if ($objectA->getObjectID() != $objectB->getObjectID()) return false;
	
		return true;
	}	
}
