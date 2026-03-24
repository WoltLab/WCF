<?php

namespace wcf\system\condition\user\activity\event;

use wcf\data\DatabaseObjectList;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\activity\event\UserActivityEventList;
use wcf\system\condition\AbstractMultiSelectCondition;
use wcf\system\condition\IObjectListCondition;
use wcf\system\WCF;

/**
 * Condition implementation for the excluded object types of user activity events.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 *
 * @implements IObjectListCondition<UserActivityEventList>
 */
class UserActivityEventExcludedObjectTypeCondition extends AbstractMultiSelectCondition implements IObjectListCondition
{
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

    #[\Override]
    public function addObjectListCondition(DatabaseObjectList $objectList, array $conditionData)
    {
        $objectList->getConditionBuilder()->add(
            'user_activity_event.objectTypeID NOT IN (?)',
            [$conditionData[$this->fieldName]]
        );
    }

    #[\Override]
    public function getOptions()
    {
        $objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.user.recentActivityEvent');

        $options = [];
        foreach ($objectTypes as $objectType) {
            $options[$objectType->objectTypeID] = WCF::getLanguage()
                ->getDynamicVariable('wcf.user.recentActivity.' . $objectType->objectType);
        }

        return $options;
    }
}
