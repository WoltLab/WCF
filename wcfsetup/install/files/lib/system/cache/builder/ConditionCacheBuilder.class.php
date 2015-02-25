<?php
namespace wcf\system\cache\builder;
use wcf\data\condition\ConditionList;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\exception\SystemException;

/**
 * Caches the conditions for a certain condition object type definition.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class ConditionCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @see	\wcf\system\cache\builder\AbstractCacheBuilder::rebuild()
	 */
	public function rebuild(array $parameters) {
		if (!isset($parameters['definitionID'])) {
			throw new SystemException("Missing 'definitionID' parameter");
		}
		
		$definition = ObjectTypeCache::getInstance()->getDefinition($parameters['definitionID']);
		if ($definition === null) {
			throw new SystemException("Unknown object type definition with id '".$parameters['definitionID']."'");
		}
		
		$objectTypes = ObjectTypeCache::getInstance()->getObjectTypes($definition->definitionName);
		if (empty($objectTypes)) {
			return array();
		}
		
		$objectTypeIDs = array();
		foreach ($objectTypes as $objectType) {
			$objectTypeIDs[] = $objectType->objectTypeID;
		}
		
		$conditionList = new ConditionList();
		$conditionList->getConditionBuilder()->add('condition_table.objectTypeID IN (?)', array($objectTypeIDs));
		$conditionList->readObjects();
		
		$groupedConditions = array();
		foreach ($conditionList as $condition) {
			if (!isset($groupedConditions[$condition->objectID])) {
				$groupedConditions[$condition->objectID] = array();
			}
			
			$groupedConditions[$condition->objectID][$condition->conditionID] = $condition;
		}
		
		return $groupedConditions;
	}
}
