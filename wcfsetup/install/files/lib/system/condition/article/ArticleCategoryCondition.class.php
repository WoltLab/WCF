<?php

namespace wcf\system\condition\article;

use wcf\data\article\ArticleList;
use wcf\data\DatabaseObjectList;
use wcf\system\condition\AbstractMultiCategoryCondition;
use wcf\system\condition\IObjectListCondition;
use wcf\system\exception\InvalidObjectArgument;

/**
 * Condition implementation for the category an article belongs to.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Condition\Article
 * @since   3.0
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
    protected $label = 'wcf.acp.article.category';

    /**
     * @inheritDoc
     */
    public function addObjectListCondition(DatabaseObjectList $objectList, array $conditionData)
    {
        if (!($objectList instanceof ArticleList)) {
            throw new InvalidObjectArgument($objectList, ArticleList::class, 'Object list');
        }

        $objectList->getConditionBuilder()->add('article.categoryID IN (?)', [$conditionData[$this->fieldName]]);
    }
}
