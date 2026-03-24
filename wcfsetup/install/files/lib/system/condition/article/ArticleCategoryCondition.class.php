<?php

namespace wcf\system\condition\article;

use wcf\data\article\ArticleList;
use wcf\data\DatabaseObjectList;
use wcf\system\condition\AbstractMultiCategoryCondition;
use wcf\system\condition\IObjectListCondition;

/**
 * Condition implementation for the category an article belongs to.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @implements IObjectListCondition<ArticleList>
 */
class ArticleCategoryCondition extends AbstractMultiCategoryCondition implements IObjectListCondition
{
    /**
     * @inheritDoc
     */
    public $objectType = 'com.woltlab.wcf.article.category';

    /**
     * @inheritDoc
     */
    protected $fieldName = 'articleCategoryIDs';

    /**
     * @inheritDoc
     */
    protected $label = 'wcf.global.category';

    #[\Override]
    public function addObjectListCondition(DatabaseObjectList $objectList, array $conditionData)
    {
        $objectList->getConditionBuilder()->add('article.categoryID IN (?)', [$conditionData[$this->fieldName]]);
    }
}
