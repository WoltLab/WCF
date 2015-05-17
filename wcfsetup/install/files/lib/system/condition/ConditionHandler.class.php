<?php
namespace wcf\system\condition;
use wcf\data\condition\ConditionAction;
use wcf\data\condition\ConditionList;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\cache\builder\ConditionCacheBuilder;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;

/**
 * Handles general condition-related matters.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.condition
 * @category	Community Framework
 */
class ConditionHandler extends SingletonFactory {
	/**
	 * list of available conditions grouped by the id of the related condition
	 * object type definition
	 * @var	array
	 */
	protected $conditions = array();
	
	/**
	 * Creates condition objects for the object with the given id and based
	 * on the given condition object types.
	 * 
	 * @param	integer						$objectID
	 * @param	array<\wcf\data\object\type\ObjectType>		$conditionObjectTypes
	 */
	public function createConditions($objectID, array $conditionObjectTypes) {
		foreach ($conditionObjectTypes as $objectType) {
			$conditionData = $objectType->getProcessor()->getData();
			if ($conditionData !== null) {
				$conditionAction = new ConditionAction(array(), 'create', array(
					'data' => array(
						'conditionData' => serialize($conditionData),
						'objectID' => $objectID,
						'objectTypeID' => $objectType->objectTypeID
					)
				));
				$conditionAction->executeAction();
			}
		}
	}
	
	/**
	 * Deletes all conditions of the objects with the given ids.
	 * 
	 * @param	string			$definitionName
	 * @param	array<integer>		$objectIDs
	 */
	public function deleteConditions($definitionName, array $objectIDs) {
		if (empty($objectIDs)) return;
		
		$definition = ObjectTypeCache::getInstance()->getDefinitionByName($definitionName);
		if ($definition === null) {
			throw new SystemException("Unknown object type definition with name '".$definitionName."'");
		}
		
		$objectTypes = ObjectTypeCache::getInstance()->getObjectTypes($definitionName);
		$objectTypeIDs = array();
		foreach ($objectTypes as $objectType) {
			$objectTypeIDs[] = $objectType->objectTypeID;
		}
		
		if (empty($objectTypeIDs)) return;
		
		$conditionList = new ConditionList();
		$conditionList->getConditionBuilder()->add('objectTypeID IN (?)', array($objectTypeIDs));
		$conditionList->getConditionBuilder()->add('objectID IN (?)', array($objectIDs));
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
	 * @return	array<\wcf\data\condition\Condition>
	 */
	public function getConditions($definitionName, $objectID) {
		// validate definition
		$definition = ObjectTypeCache::getInstance()->getDefinitionByName($definitionName);
		if ($definition === null) {
			throw new SystemException("Unknown object type definition with name '".$definitionName."'");
		}
		
		if (!isset($this->conditions[$definition->definitionID])) {
			$this->conditions[$definition->definitionID] = ConditionCacheBuilder::getInstance()->getData(array(
				'definitionID' => $definition->definitionID
			));
		}
		
		if (isset($this->conditions[$definition->definitionID][$objectID])) {
			return $this->conditions[$definition->definitionID][$objectID];
		}
		
		return array();
	}
	
	/**
	 * Updates the conditions for the object with the given object id.
	 * 
	 * @param	integer						$objectID
	 * @param	array<\wcf\data\condition\Condition>		$oldConditions
	 * @param	array<\wcf\data\object\type\ObjectType>		$conditionObjectTypes
	 */
	public function updateConditions($objectID, array $oldConditions, array $conditionObjectTypes) {
		// delete old conditions first
		$conditionAction = new ConditionAction($oldConditions, 'delete');
		$conditionAction->executeAction();
		
		// create new conditions
		$this->createConditions($objectID, $conditionObjectTypes);
	}
}
