<?php

namespace wcf\system\condition\user\trophy;

use wcf\data\DatabaseObjectList;
use wcf\data\trophy\TrophyCache;
use wcf\data\user\trophy\UserTrophyList;
use wcf\system\condition\AbstractMultiSelectCondition;
use wcf\system\condition\IObjectListCondition;

/**
 * Condition implementation for the excluded trophies.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @implements IObjectListCondition<UserTrophyList>
 */
class UserTrophyExcludedTrophiesCondition extends AbstractMultiSelectCondition implements IObjectListCondition
{
    /**
     * @inheritDoc
     */
    protected $description = 'wcf.global.multiSelect';

    /**
     * @inheritDoc
     */
    protected $fieldName = 'userTrophyExcludedTrophies';

    /**
     * @inheritDoc
     */
    protected $label = 'wcf.user.trophy.condition.excludedTrophies';

    #[\Override]
    public function addObjectListCondition(DatabaseObjectList $objectList, array $conditionData)
    {
        $objectList->getConditionBuilder()->add('user_trophy.trophyID NOT IN (?)', [$conditionData[$this->fieldName]]);
    }

    #[\Override]
    public function getOptions()
    {
        $trophies = TrophyCache::getInstance()->getTrophies();

        $options = [];
        foreach ($trophies as $trophy) {
            $options[$trophy->trophyID] = $trophy->getTitle();
        }

        \asort($options);

        return $options;
    }
}
