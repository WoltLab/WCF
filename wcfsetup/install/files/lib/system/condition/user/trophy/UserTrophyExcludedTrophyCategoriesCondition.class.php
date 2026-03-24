<?php

namespace wcf\system\condition\user\trophy;

use wcf\data\DatabaseObjectList;
use wcf\data\trophy\category\TrophyCategoryCache;
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
class UserTrophyExcludedTrophyCategoriesCondition extends AbstractMultiSelectCondition implements IObjectListCondition
{
    /**
     * @inheritDoc
     */
    protected $description = 'wcf.global.multiSelect';

    /**
     * @inheritDoc
     */
    protected $fieldName = 'userTrophyExcludedTrophyCategories';

    /**
     * @inheritDoc
     */
    protected $label = 'wcf.user.trophy.condition.excludedTrophyCategories';

    #[\Override]
    public function addObjectListCondition(DatabaseObjectList $objectList, array $conditionData)
    {
        $objectList->getConditionBuilder()->add('trophy.categoryID NOT IN (?)', [$conditionData[$this->fieldName]]);
    }

    #[\Override]
    public function getOptions()
    {
        $categories = TrophyCategoryCache::getInstance()->getCategories();

        $options = [];
        foreach ($categories as $category) {
            $options[$category->categoryID] = $category->getTitle();
        }

        \asort($options);

        return $options;
    }
}
