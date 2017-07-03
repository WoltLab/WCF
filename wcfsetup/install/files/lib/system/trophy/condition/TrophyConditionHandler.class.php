<?php
namespace wcf\system\trophy\condition;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\SingletonFactory;

/**
 * Handles trophy conditions. 
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Trophy\Condition
 * @since	3.1
 */
class TrophyConditionHandler extends SingletonFactory {
	/**
	 * definition name for trophy conditions
	 * @var string
	 */
	const CONDITION_DEFINITION_NAME = 'com.woltlab.wcf.condition.trophy';
	
	/**
	 * list of grouped trophy condition object types
	 * @var	ObjectType[][]
	 */
	protected $groupedObjectTypes = [];
	
	/**
	 * @inheritDoc
	 */
	protected function init() {
		$objectTypes = ObjectTypeCache::getInstance()->getObjectTypes(self::CONDITION_DEFINITION_NAME);
		
		foreach ($objectTypes as $objectType) {
			if (!$objectType->conditiongroup) continue;
			
			if (!isset($this->groupedObjectTypes[$objectType->conditiongroup])) {
				$this->groupedObjectTypes[$objectType->conditiongroup] = [];
			}
			
			$this->groupedObjectTypes[$objectType->conditiongroup][$objectType->objectTypeID] = $objectType;
		}
	}
	
	/**
	 * Returns the list of grouped trophy condition object types.
	 *
	 * @return	ObjectType[][]
	 */
	public function getGroupedObjectTypes() {
		return $this->groupedObjectTypes;
	}
}
