<?php
namespace wcf\data;
use wcf\system\WCF;

/**
 * Abstract class for all data holder classes.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data
 */
abstract class DatabaseObject implements IIDObject, IStorableObject {
	/**
	 * database table for this object
	 * @var	string
	 */
	protected static $databaseTableName = '';
	
	/**
	 * indicates if database table index is an identity column
	 * @var	bool
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
	 * @param	mixed			$id
	 * @param	array			$row
	 * @param	DatabaseObject		$object
	 */
	public function __construct($id, array $row = null, DatabaseObject $object = null) {
		if ($id !== null) {
			$sql = "SELECT	*
				FROM	".static::getDatabaseTableName()."
				WHERE	".static::getDatabaseTableIndexName()." = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$id]);
			$row = $statement->fetchArray();
			
			// enforce data type 'array'
			if ($row === false) $row = [];
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
	 * @inheritDoc
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
	 * @return	int
	 */
	public function getObjectID() {
		return $this->data[static::getDatabaseTableIndexName()];
	}
	
	/**
	 * @inheritDoc
	 */
	public function __isset($name) {
		return isset($this->data[$name]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getData() {
		return $this->data;
	}
	
	/**
	 * @inheritDoc
	 */
	public static function getDatabaseTableName() {
		$className = get_called_class();
		$classParts = explode('\\', $className);
		
		if (static::$databaseTableName !== '') {
			return $classParts[0].WCF_N.'_'.static::$databaseTableName;
		}
		
		static $databaseTableNames = [];
		if (!isset($databaseTableNames[$className])) {
			$databaseTableNames[$className] = $classParts[0].WCF_N.'_'.strtolower(implode('_', preg_split('~(?=[A-Z](?=[a-z]))~', array_pop($classParts), -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY)));
		}
		
		return $databaseTableNames[$className];
	}
	
	/**
	 * @inheritDoc
	 */
	public static function getDatabaseTableAlias() {
		if (static::$databaseTableName !== '') {
			return static::$databaseTableName;
		}
		
		$className = get_called_class();
		static $databaseTableAliases = [];
		if (!isset($databaseTableAliases[$className])) {
			$classParts = explode('\\', $className);
			$databaseTableAliases[$className] = strtolower(implode('_', preg_split('~(?=[A-Z](?=[a-z]))~', array_pop($classParts), -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY)));
		}
		
		return $databaseTableAliases[$className];
	}
	
	/**
	 * @inheritDoc
	 */
	public static function getDatabaseTableIndexIsIdentity() {
		return static::$databaseTableIndexIsIdentity;
	}
	
	/**
	 * @inheritDoc
	 */
	public static function getDatabaseTableIndexName() {
		if (static::$databaseTableIndexName !== '') {
			return static::$databaseTableIndexName;
		}
		
		static $databaseTableIndexName = null;
		if ($databaseTableIndexName === null) {
			$className = explode('\\', get_called_class());
			$parts = preg_split('~(?=[A-Z](?=[a-z]))~', array_pop($className), -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
			$databaseTableIndexName = strtolower(array_pop($parts)).'ID';
		}
		
		return $databaseTableIndexName;
	}
	
	/**
	 * Sorts a list of database objects.
	 * 
	 * @param	DatabaseObject[]	$objects
	 * @param	mixed			$sortBy
	 * @param	string			$sortOrder
	 * @param	bool			$maintainIndexAssociation
	 */
	public static function sort(&$objects, $sortBy, $sortOrder = 'ASC', $maintainIndexAssociation = true) {
		$sortArray = $objects2 = [];
		foreach ($objects as $idx => $obj) {
			/** @noinspection PhpVariableVariableInspection */
			$sortArray[$idx] = $obj->$sortBy;
			
			// array_multisort will drop index association if key is not a string
			if ($maintainIndexAssociation) {
				$objects2[$idx.'x'] = $obj;
			}
		}
		
		if ($maintainIndexAssociation) {
			$objects = [];
			array_multisort($sortArray, $sortOrder == 'ASC' ? SORT_ASC : SORT_DESC, $objects2);
			
			$objects = [];
			foreach ($objects2 as $idx => $obj) {
				$objects[substr($idx, 0, -1)] = $obj;
			}
		}
		else {
			array_multisort($sortArray, $sortOrder == 'ASC' ? SORT_ASC : SORT_DESC, $objects);
		}
	}
}
