<?php
namespace wcf\data;
use wcf\system\exception\SystemException;

/**
 * Basic implementation for object decorators.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data
 * @category	Community Framework
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
	
	/**
	 * Creates a new DatabaseObjectDecorator object.
	 * 
	 * @param	\wcf\data\DatabaseObject		$object
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
	 * @see	\wcf\data\IStorableObject::__get()
	 */
	public function __get($name) {
		return $this->object->__get($name);
	}
	
	/**
	 * @see	\wcf\data\IStorableObject::__isset()
	 */
	public function __isset($name) {
		return $this->object->__isset($name);
	}
	
	/**
	 * @see	\wcf\data\DatabaseObject::getObjectID()
	 */
	public function getObjectID() {
		return $this->object->getObjectID();
	}
	
	/**
	 * @see	\wcf\data\IStorableObject::getData()
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
	 */
	public function __call($name, $arguments) {
		if (!method_exists($this->object, $name) && !($this->object instanceof DatabaseObjectDecorator)) {
			throw new SystemException("unknown method '".$name."'");
		}
		
		return call_user_func_array(array($this->object, $name), $arguments);
	}
	
	/**
	 * @see	\wcf\data\IStorableObject::getDatabaseTableAlias()
	 */
	public static function getDatabaseTableAlias() {
		return call_user_func(array(static::$baseClass, 'getDatabaseTableAlias'));
	}
	
	/**
	 * @see	\wcf\data\IStorableObject::getDatabaseTableName()
	 */
	public static function getDatabaseTableName() {
		return call_user_func(array(static::$baseClass, 'getDatabaseTableName'));
	}
	
	/**
	 * @see	\wcf\data\IStorableObject::getDatabaseTableIndexIsIdentity()
	 */
	public static function getDatabaseTableIndexIsIdentity() {
		return call_user_func(array(static::$baseClass, 'getDatabaseTableIndexIsIdentity'));
	}
	
	/**
	 * @see	\wcf\data\IStorableObject::getDatabaseTableIndexName()
	 */
	public static function getDatabaseTableIndexName() {
		return call_user_func(array(static::$baseClass, 'getDatabaseTableIndexName'));
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
