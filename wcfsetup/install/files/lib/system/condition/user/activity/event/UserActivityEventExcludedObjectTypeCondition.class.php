<?php
namespace wcf\system\condition\user\activity\event;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\activity\event\UserActivityEventList;
use wcf\data\DatabaseObjectList;
use wcf\system\condition\AbstractMultiSelectCondition;
use wcf\system\condition\IObjectListCondition;
use wcf\system\WCF;

/**
 * Condition implementation for the excluded object types of user activity events.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Condition\User\Activity\Event
 * @since	3.0
 */
class UserActivityEventExcludedObjectTypeCondition extends AbstractMultiSelectCondition implements IObjectListCondition {
	/**
	 * @inheritDoc
	 */
	protected $description = 'wcf.global.multiSelect';
	
	/**
	 * @inheritDoc
	 */
	protected $fieldName = 'userActivityEventExcludedObjectTypeID';
	
	/**
	 * @inheritDoc
	 */
	protected $label = 'wcf.user.recentActivity.condition.excludedObjectType';
	
	/**
	 * @inheritDoc
	 */
	public function addObjectListCondition(DatabaseObjectList $objectList, array $conditionData) {
		if (!($objectList instanceof UserActivityEventList)) {
			throw new \InvalidArgumentException("Object list is no instance of '".UserActivityEventList::class."', instance of '".get_class($objectList)."' given.");
		}
		
		$objectList->getConditionBuilder()->add('user_activity_event.objectTypeID NOT IN (?)', [$conditionData[$this->fieldName]]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getOptions() {
		$objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.user.recentActivityEvent');
		
		$options = [];
		foreach ($objectTypes as $objectType) {
			$options[$objectType->objectTypeID] = WCF::getLanguage()->getDynamicVariable('wcf.user.recentActivity.' . $objectType->objectType);
		}
		
		return $options;
	}
}
