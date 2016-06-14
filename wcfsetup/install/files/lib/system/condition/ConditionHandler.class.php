<?php
namespace wcf\system\condition;
use wcf\data\condition\Condition;
use wcf\data\condition\ConditionAction;
use wcf\data\condition\ConditionList;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\cache\builder\ConditionCacheBuilder;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;

/**
 * Handles general condition-related matters.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Condition
 */
class ConditionHandler extends SingletonFactory {
	/**
	 * list of available conditions grouped by the id of the related condition
	 * object type definition
	 * @var	array
	 */
	protected $conditions = [];
	
	/**
	 * Creates condition objects for the object with the given id and based
	 * on the given condition object types.
	 * 
	 * @param	integer		$objectID
	 * @param	ObjectType[]	$conditionObjectTypes
	 */
	public function createConditions($objectID, array $conditionObjectTypes) {
		foreach ($conditionObjectTypes as $objectType) {
			$conditionData = $objectType->getProcessor()->getData();
			if ($conditionData !== null) {
				$conditionAction = new ConditionAction([], 'create', [
					'data' => [
						'conditionData' => serialize($conditionData),
						'objectID' => $objectID,
						'objectTypeID' => $objectType->objectTypeID
					]
				]);
				$conditionAction->executeAction();
			}
		}
	}
	
	/**
	 * Deletes all conditions of the objects with the given ids.
	 * 
	 * @param	string		$definitionName
	 * @param	integer[]	$objectIDs
	 * @throws	SystemException
	 */
	public function deleteConditions($definitionName, array $objectIDs) {
		if (empty($objectIDs)) return;
		
		$definition = ObjectTypeCache::getInstance()->getDefinitionByName($definitionName);
		if ($definition === null) {
			throw new SystemException("Unknown object type definition with name '".$definitionName."'");
		}
		
		$objectTypes = ObjectTypeCache::getInstance()->getObjectTypes($definitionName);
		$objectTypeIDs = [];
		foreach ($objectTypes as $objectType) {
			$objectTypeIDs[] = $objectType->objectTypeID;
		}
		
		if (empty($objectTypeIDs)) return;
		
		$conditionList = new ConditionList();
		$conditionList->getConditionBuilder()->add('objectTypeID IN (?)', [$objectTypeIDs]);
		$conditionList->getConditionBuilder()->add('objectID IN (?)', [$objectIDs]);
		$conditionList->readObjects();
		
		if (count($conditionList)) {
			$conditionAction = new ConditionAction($conditionList->getObjects(), 'delete');
			$conditionAction->executeAction();
		}
	}
	
	/**
	 * Returns the conditions for the conditioned object with the given condition
	 * object type definition and object id.
	 * 
	 * @param	string		$definitionName
	 * @param	integer		$objectID
	 * @return	Condition[]
	 * @throws	SystemException
	 */
	public function getConditions($definitionName, $objectID) {
		// validate definition
		$definition = ObjectTypeCache::getInstance()->getDefinitionByName($definitionName);
		if ($definition === null) {
			throw new SystemException("Unknown object type definition with name '".$definitionName."'");
		}
		
		if (!isset($this->conditions[$definition->definitionID])) {
			$this->conditions[$definition->definitionID] = ConditionCacheBuilder::getInstance()->getData([
				'definitionID' => $definition->definitionID
			]);
		}
		
		if (isset($this->conditions[$definition->definitionID][$objectID])) {
			return $this->conditions[$definition->definitionID][$objectID];
		}
		
		return [];
	}
	
	/**
	 * Updates the conditions for the object with the given object id.
	 * 
	 * @param	integer		$objectID
	 * @param	Condition[]	$oldConditions
	 * @param	ObjectType[]	$conditionObjectTypes
	 */
	public function updateConditions($objectID, array $oldConditions, array $conditionObjectTypes) {
		// delete old conditions first
		$conditionAction = new ConditionAction($oldConditions, 'delete');
		$conditionAction->executeAction();
		
		// create new conditions
		$this->createConditions($objectID, $conditionObjectTypes);
	}
}
