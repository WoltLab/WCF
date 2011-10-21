<?php
namespace wcf\data\object\type;
use wcf\data\ProcessibleDatabaseObject;
use wcf\system\exception\SystemException;
use wcf\util\ClassUtil;

/**
 * Represents an object type.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.object.type
 * @category 	Community Framework
 */
class ObjectType extends ProcessibleDatabaseObject {
	/**
	 * @see	wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'object_type';
	
	/**
	 * @see	wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'objectTypeID';
	
	/**
	 * @see	wcf\data\ProcessibleDatabaseObject::getProcessor()
	 */
	public function getProcessor() {
		if ($this->processor === null) {
			if ($this->className) {
				if (!class_exists($this->className)) {
					throw new SystemException("Unable to find class '".$this->className."'");
				}
				/*
					TODO:
					Why should the class implement IDatabaseObjectProcessor? Given the fact,
					that the default implementation IObjectTypeProvider does not decorate the
					objects itself - instead it provides methods to receive the required objects.
				
				if (!ClassUtil::isInstanceOf($this->className, 'wcf\data\IDatabaseObjectProcessor')) {
					throw new SystemException("'".$this->className."' should implement wcf\data\IDatabaseObjectProcessor");
				}
				*/
				if (($definitionInterface = ObjectTypeCache::getInstance()->getDefinition($this->definitionID)->interfaceName) && !ClassUtil::isInstanceOf($this->className, $definitionInterface)) {
					throw new SystemException("'".$this->className."' should implement ".$definitionInterface);
				}
				
				$this->processor = new $this->className($this);
			}
		}
		
		return $this->processor;
	}
}
