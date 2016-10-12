<?php
namespace wcf\data\object\type;
use wcf\data\DatabaseObjectList;

/**
 * Abstract implementation of an object type provider.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Object\Type
 */
abstract class AbstractObjectTypeProvider implements IObjectTypeProvider {
	/**
	 * class name of the provided database objects
	 * @var	string
	 */
	public $className = '';
	
	/**
	 * name of the class which decorates the provided database objects
	 * @var	string
	 */
	public $decoratorClassName = '';
	
	/**
	 * list class name of the provided database objects
	 * @var	string
	 */
	public $listClassName = '';
	
	/**
	 * @inheritDoc
	 */
	public function getObjectByID($objectID) {
		$object = new $this->className($objectID);
		if ($this->decoratorClassName) {
			$object = new $this->decoratorClassName($object);
		}
		
		return $object;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getObjectsByIDs(array $objectIDs) {
		$tableAlias = call_user_func([$this->className, 'getDatabaseTableAlias']);
		$tableIndex = call_user_func([$this->className, 'getDatabaseTableIndexName']);
		
		/** @var DatabaseObjectList $objectList */
		$objectList = new $this->listClassName();
		if ($this->decoratorClassName) {
			$objectList->decoratorClassName = $this->decoratorClassName;
		}
		$objectList->getConditionBuilder()->add($tableAlias.".".$tableIndex." IN (?)", [$objectIDs]);
		$objectList->readObjects();
		
		return $objectList->getObjects();
	}
}
