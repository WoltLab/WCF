<?php
namespace wcf\data\object\type;

/**
 * Abstract implementation of an object type provider.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.object.type
 * @category	Community Framework
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
	 * @see	\wcf\data\object\type\IObjectTypeProvider::getObjectByID()
	 */
	public function getObjectByID($objectID) {
		$object = new $this->className($objectID);
		if ($this->decoratorClassName) {
			$object = new $this->decoratorClassName($object);
		}
		
		return $object;
	}
	
	/**
	 * @see	\wcf\data\object\type\IObjectTypeProvider::getObjectsByIDs()
	 */
	public function getObjectsByIDs(array $objectIDs) {
		$tableAlias = call_user_func(array($this->className, 'getDatabaseTableAlias'));
		$tableIndex = call_user_func(array($this->className, 'getDatabaseTableIndexName'));
		
		$objectList = new $this->listClassName();
		if ($this->decoratorClassName) {
			$objectList->decoratorClassName = $this->decoratorClassName;
		}
		$objectList->getConditionBuilder()->add($tableAlias.".".$tableIndex." IN (?)", array($objectIDs));
		$objectList->readObjects();
		
		return $objectList->getObjects();
	}
}
