<?php
namespace wcf\data;
use wcf\system\exception\SystemException;

/**
 * Basic implementation for object decorators.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data
 */
abstract class DatabaseObjectDecorator extends DatabaseObject {
	/**
	 * name of the base class
	 * @var	string
	 */
	protected static $baseClass = '';
	
	/**
	 * decorated object
	 * @var	\wcf\data\DatabaseObject
	 */
	protected $object = null;
	
	/** @noinspection PhpMissingParentConstructorInspection */
	/**
	 * Creates a new DatabaseObjectDecorator object.
	 * 
	 * @param	DatabaseObject		$object
	 * @throws	SystemException
	 */
	public function __construct(DatabaseObject $object) {
		if (empty(static::$baseClass)) {
			throw new SystemException('Base class not specified');
		}
		
		if (!($object instanceof static::$baseClass)) {
			throw new SystemException("Object does not match '".static::$baseClass."' (given object is of class '".get_class($object)."')");
		}
		
		$this->object = $object;
	}
	
	/**
	 * @inheritDoc
	 */
	public function __get($name) {
		return $this->object->__get($name);
	}
	
	/**
	 * @inheritDoc
	 */
	public function __isset($name) {
		return $this->object->__isset($name);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getObjectID() {
		return $this->object->getObjectID();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getData() {
		return $this->object->getData();
	}
	
	/**
	 * Delegates inaccessible methods calls to the decorated object.
	 * 
	 * @param	string		$name
	 * @param	array		$arguments
	 * @return	mixed
	 * @throws	SystemException
	 */
	public function __call($name, $arguments) {
		if (!method_exists($this->object, $name) && !($this->object instanceof DatabaseObjectDecorator)) {
			throw new SystemException("unknown method '".$name."'");
		}
		
		return call_user_func_array([$this->object, $name], $arguments);
	}
	
	/**
	 * @inheritDoc
	 */
	public static function getDatabaseTableAlias() {
		return call_user_func([static::$baseClass, 'getDatabaseTableAlias']);
	}
	
	/**
	 * @inheritDoc
	 */
	public static function getDatabaseTableName() {
		return call_user_func([static::$baseClass, 'getDatabaseTableName']);
	}
	
	/**
	 * @inheritDoc
	 */
	public static function getDatabaseTableIndexIsIdentity() {
		return call_user_func([static::$baseClass, 'getDatabaseTableIndexIsIdentity']);
	}
	
	/**
	 * @inheritDoc
	 */
	public static function getDatabaseTableIndexName() {
		return call_user_func([static::$baseClass, 'getDatabaseTableIndexName']);
	}
	
	/**
	 * Returns the name of the base class.
	 * 
	 * @return	string
	 */
	public static function getBaseClass() {
		return static::$baseClass;
	}
	
	/**
	 * Returns the decorated object
	 * 
	 * @return	\wcf\data\DatabaseObject
	 */
	public function getDecoratedObject() {
		return $this->object;
	}
}
