<?php
namespace wcf\data\object\type;
use wcf\data\ProcessibleDatabaseObject;
use wcf\data\TDatabaseObjectOptions;
use wcf\data\TDatabaseObjectPermissions;
use wcf\system\exception\SystemException;

/**
 * Represents an object type.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.object.type
 * @category	Community Framework
 *
 * @property-read	integer		$objectTypeID
 * @property-read	integer		$definitionID
 * @property-read	integer		$packageID
 * @property-read	string		$objectType
 * @property-read	string		$className
 * @property-read	array		$additionalData
 */
class ObjectType extends ProcessibleDatabaseObject {
	use TDatabaseObjectOptions;
	use TDatabaseObjectPermissions;
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'object_type';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'objectTypeID';
	
	/**
	 * @see	\wcf\data\IStorableObject::__get()
	 */
	public function __get($name) {
		$value = parent::__get($name);
		
		// treat additional data as data variables if it is an array
		if ($value === null) {
			if (is_array($this->data['additionalData']) && isset($this->data['additionalData'][$name])) {
				$value = $this->data['additionalData'][$name];
			}
		}
		
		return $value;
	}
	
	/**
	 * Returns the names of proporties that should be serialized.
	 * 
	 * @return	string[]
	 */
	public final function __sleep() {
		// 'processor' isn't returned since it can be an instance of
		// wcf\system\SingletonFactory which may not be serialized
		return array('data');
	}
	
	/**
	 * @see	\wcf\data\DatabaseObject::handleData()
	 */
	protected function handleData($data) {
		parent::handleData($data);
		
		$this->data['additionalData'] = @unserialize($this->data['additionalData']);
		if (!is_array($this->data['additionalData'])) {
			$this->data['additionalData'] = array();
		}
	}
	
	/**
	 * @see	\wcf\data\ProcessibleDatabaseObject::getProcessor()
	 */
	public function getProcessor() {
		if ($this->processor === null) {
			if ($this->className) {
				if (!class_exists($this->className)) {
					throw new SystemException("Unable to find class '".$this->className."'");
				}
				if (($definitionInterface = ObjectTypeCache::getInstance()->getDefinition($this->definitionID)->interfaceName) && !is_subclass_of($this->className, $definitionInterface)) {
					throw new SystemException("'".$this->className."' does not implement '".$definitionInterface."'");
				}
				
				if (is_subclass_of($this->className, 'wcf\system\SingletonFactory')) {
					$this->processor = call_user_func(array($this->className, 'getInstance'));
				}
				else {
					$this->processor = new $this->className($this);
				}
			}
		}
		
		return $this->processor;
	}
}
