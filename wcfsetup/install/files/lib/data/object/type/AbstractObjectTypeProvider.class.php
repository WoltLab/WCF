<?php
namespace wcf\data\object\type;

/**
 * Basic implementation for object type providers.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.object.type
 * @category 	Community Framework
 */
abstract class AbstractObjectTypeProvider implements IObjectTypeProvider {
	/**
	 * DatabaseObject class name
	 * @var	string
	 */
	public $className = '';
	
	/**
	 * DatabaseObjectList class name
	 * @var	string
	 */
	public $listClassName = '';
	
	/**
	 * @see	wcf\data\object\type\IObjectTypeProvider::getObjectByID()
	 */
	public function getObjectByID($objectID) {
		return new $this->className($objectID);
	}

	/**
	 * @see	wcf\data\object\type\IObjectTypeProvider::getObjectsByIDs()
	 */
	public function getObjectsByIDs(array $objectIDs) {
		$tableAlias = call_user_func($this->className, 'getDatabaseTableAlias');
		$tableIndex = call_user_func($this->className, 'getDatabaseTableIndexName');
		
		$objectList = new $this->listClassName();
		$objectList->getConditionBuilder()->add($tableAlias.".".$tableIndex." IN (?)", array($objectIDs));
		$objectList->sqlLimit = 0;
		$objectList->readObjects();
		
		return $objectList->getObjects();
	}
}
