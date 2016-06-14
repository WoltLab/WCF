<?php
namespace wcf\data\object\type;
use wcf\data\object\type\definition\ObjectTypeDefinition;
use wcf\data\ProcessibleDatabaseObject;
use wcf\data\TDatabaseObjectOptions;
use wcf\data\TDatabaseObjectPermissions;
use wcf\system\exception\ImplementationException;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;

/**
 * Represents an object type.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Object\Type
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
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'object_type';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'objectTypeID';
	
	/**
	 * @inheritDoc
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
		return ['data'];
	}
	
	/**
	 * @inheritDoc
	 */
	protected function handleData($data) {
		parent::handleData($data);
		
		$this->data['additionalData'] = @unserialize($this->data['additionalData']);
		if (!is_array($this->data['additionalData'])) {
			$this->data['additionalData'] = [];
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function getProcessor() {
		if ($this->processor === null) {
			if ($this->className) {
				if (!class_exists($this->className)) {
					throw new SystemException("Unable to find class '".$this->className."'");
				}
				if (($definitionInterface = ObjectTypeCache::getInstance()->getDefinition($this->definitionID)->interfaceName) && !is_subclass_of($this->className, $definitionInterface)) {
					throw new ImplementationException($this->className, $definitionInterface);
				}
				
				if (is_subclass_of($this->className, SingletonFactory::class)) {
					$this->processor = call_user_func([$this->className, 'getInstance']);
				}
				else {
					$this->processor = new $this->className($this);
				}
			}
		}
		
		return $this->processor;
	}
	
	/**
	 * Returns the object type definition of the object type.
	 * 
	 * @return	ObjectTypeDefinition
	 * @since	3.0
	 */
	public function getDefinition() {
		return ObjectTypeCache::getInstance()->getDefinition($this->definitionID);
	}
}
