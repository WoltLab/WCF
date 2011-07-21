<?php
namespace wcf\data;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

/**
 * Basic implementation for object decorators.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data
 * @category 	Community Framework
 */
abstract class DatabaseObjectDecorator extends DatabaseObject {
	/**
	 * name of the base class
	 * @var string
	 */
	protected static $baseClass = '';
	
	/**
	 * the object being decorated
	 * @var DatabaseObject
	 */
	protected $object = null;
	
	/**
	 * Creates a new DatabaseObjectDecorator object.
	 * 
	 * @param	DatabaseObject		$object
	 */
	public function __construct(DatabaseObject $object) {
		if (empty(static::$baseClass)) {
			throw new SystemException('Base class not specified');
		}
		
		if (!($object instanceof static::$baseClass)) {
			throw new SystemException('Object does not match '.static::$baseClass);
		}
		
		$this->object = $object;
	}
	
	/**
	 * @see DatabaseObject::__get()
	 */
	public function __get($name) {
		return $this->object->__get($name);
	}
	
	/**
	 * @see DatabaseObject::__isset()
	 */
	public function __isset($name) {
		return $this->object->__isset($name);
	}
	
	/**
	 * Delegates inaccessible methods calls to the decorated object.
	 *  
	 * @param	string		$name
	 * @param	array		$arguments
	 * @return	mixed
	 */
	public function __call($name, $arguments) {
		if (!method_exists($this->object, $name)) {
			throw new SystemException("unknown method '".$name."'");
		}
		
		return call_user_func_array(array($this->object, $name), $arguments);
	}
	
	/**
	 * @see StorableObject::getDatabaseTableName()
	 */
	public static function getDatabaseTableName() {
		return call_user_func(array(static::$baseClass, 'getDatabaseTableName'));
	}
	
	/**
	 * @see	StorableObject::getDatabaseTableIndexIsIdentity()
	 */
	public static function getDatabaseTableIndexIsIdentity() {
		return call_user_func(array(static::$baseClass, 'getDatabaseTableIndexIsIdentity'));
	}
	
	/**
	 * @see StorableObject::getDatabaseTableIndexName()
	 */
	public static function getDatabaseTableIndexName() {
		return call_user_func(array(static::$baseClass, 'getDatabaseTableIndexName'));
	}
	
	/**
	 * Returns the name of the base class.
	 * 
	 * @return string
	 */
	public static function getBaseClass() {
		return static::$baseClass;
	}
	
	/**
	 * Returns the decorated object
	 * 
	 * @return wcf\data\DatabaseObject
	 */
	public function getDecoratedObject() {
		return $this->object;
	}
}
