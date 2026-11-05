<?php

namespace wcf\system\condition\article;

use wcf\data\article\ArticleList;
use wcf\data\DatabaseObjectList;
use wcf\system\condition\AbstractCheckboxCondition;
use wcf\system\condition\IObjectListCondition;

/**
 * Condition implementation for articles to only include featured articles.
 *
 * @author  Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   6.3
 *
 * @implements IObjectListCondition<ArticleList>
 */
class ArticleFeaturedCondition extends AbstractCheckboxCondition implements IObjectListCondition
{
    /**
     * @inheritDoc
     */
    protected $fieldName = 'articleIsFeatured';

    /**
     * @inheritDoc
     */
    protected $label = 'wcf.article.condition.isFeatured';

    #[\Override]
    public function addObjectListCondition(DatabaseObjectList $objectList, array $conditionData): void
    {
        $objectList->getConditionBuilder()->add('article.isFeatured = ?', [1]);
    }
}
