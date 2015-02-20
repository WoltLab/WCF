<?php
namespace wcf\data;
use wcf\system\WCF;

/**
 * Abstract class for all data holder classes.
 * 
 * @author	Marcel Werk, Sebastian Teumert
 * @copyright	2001-2014 WoltLab GmbH
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
	 * @param	\wcf\data\DatabaseObject		$object
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
	 * @see	\wcf\data\IStorableObject::__get()
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
	 * @see	\wcf\data\IStorableObject::__isset()
	 */
	public function __isset($name) {
		return isset($this->data[$name]);
	}
	
	/**
	 * @see	\wcf\data\IStorableObject::getData()
	 */
	public function getData() {
		return $this->data;
	}
	
	/**
	 * Returns the database table name of this object.
	 * 
	 * If the DBO defines a static, non-empty $databaseTableName property, that property
	 * is used and prefixed with the application abbreviation and WCF instance number.
	 *
	 * If the property is not defined, the table name is guessed. Therefore,
	 * the class name is split into its CamelCase parts and rejoined with under-
	 * scores (_). 
	 * 
	 * Example:
	 * wcf\data\event\EventListener would be automatically guessed to
	 * wcf<WCF_N>_event_listener
	 *
	 * @see	\wcf\data\IStorableObject::getDatabaseTableName()
	 * @return string <app_abbreviation><WCF_N>_<table_name>
	 */
	public static function getDatabaseTableName() {
		$classParts = explode('\\', get_called_class());
		$tableName = static::doTableNameGuessing();
		return $classParts[0].WCF_N.'_'.$tableName;
	}
	
	/**
	 * Returns the database table alias of the object.
	 *
	 * if the DBO defines a static, non-empty $databaseTableName property, 
	 * that property is used and returned.
	 *
	 * Otherwise, the table alias is guessed. Therefore the class name is split
	 * into its CamelCase parts and rejoined with underscores.
	 * 
	 * Example:
	 * wcf\data\event\EventListener would be automatically guessed to
	 * event_listener
	 *
	 * @see	\wcf\data\IStorableObject::getDatabaseTableAlias()
	 * @return string
	 */
	public static function getDatabaseTableAlias() {
		return static::doTableNameGuessing();
	}
	
	/**
	 * Guesses the table name. 
	 *
	 * This method is private & final because it should
	 * never be overridden. Developers can override {@link getDatabaseTableAlias()}
	 * and {@link getDatabaseTableName()} separately when needed, as is done for example
	 * in {@link wcf\data\user\User}.
	 * 
	 * The table name is guessed by splitting the CamelCase class name into it's
	 * parts and then rejoining them with underscores (_). This works
	 * well in most cases, but fails for example with {@link wcf\data\acp\ACPSession}
	 * because of the multiple upper case characters. In those cases, guessing 
	 * can be prevented by defining a static $databaseTableName property in
	 * the class.
	 * 
	 * @return string
	 */
	private static final function doTableNameGuessing() {
		$className = get_called_class();
		if (property_exists($className, 'databaseTableName') && !empty(static::$databaseTableName)) {
			$tableName = static::$databaseTableName;
		}
		else {
			$classParts = explode('\\', $className);
			$tableName = strtolower(implode('_', preg_split('/(?=[A-Z])/', array_pop($classParts), -1, PREG_SPLIT_NO_EMPTY)));
		}
		return $tableName;
	}
	
	/**
	 * @see	\wcf\data\IStorableObject::getDatabaseTableIndexIsIdentity()
	 */
	public static function getDatabaseTableIndexIsIdentity() {
		return static::$databaseTableIndexIsIdentity;
	}
	
	/**
	 * Returns the name of the index column of this DBO.
	 * 
	 * If the class defined a static, non-empty $databaseTableIndexName property,
	 * that property is returned. Otherwise the index is guessed.
	 *
	 * The guessed index is the last part of the CamelCase class name followed
	 * by the literal "ID". 
	 * For example, wcf\data\event\listener\EventListener would be guessed to "listenerID".
	 *
	 * @see	\wcf\data\IStorableObject::getDatabaseTableIndexName()
	 * @return string
	 */
	public static function getDatabaseTableIndexName() {
		$className = get_called_class();
		if (property_exists($className, 'databaseTableIndexName') && !empty(static::$databaseTableIndexName)) {
			$indexName = static::$databaseTableIndexName;
		}
		else {
			$classParts = explode('\\', $className);
			$classNameParts = preg_split('/(?=[A-Z])/', array_pop($classParts), -1, PREG_SPLIT_NO_EMPTY);
			$indexName = strtolower(array_pop($classNameParts)) . 'ID';
		}
		return $indexName;
	}
	
	/**
	 * Sorts a list of database objects.
	 * 
	 * @param	array<\wcf\data\DatabaseObject>	$objects
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
}
