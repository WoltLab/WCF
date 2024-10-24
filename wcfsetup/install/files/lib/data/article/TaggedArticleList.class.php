<?php

namespace wcf\data\article;

use wcf\data\tag\Tag;
use wcf\system\tagging\TagEngine;

/**
 * Represents a list of tagged articles.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 */
class TaggedArticleList extends AccessibleArticleList
{
    /**
     * Creates a new CategoryArticleList object.
     *
     * @param Tag|Tag[] $tags
     */
    public function __construct($tags)
    {
        parent::__construct();

        $this->sqlOrderBy = 'article.time ' . ARTICLE_SORT_ORDER;

        $subselect = TagEngine::getInstance()->getSubselectForObjectsByTags(
            'com.woltlab.wcf.article',
            $tags
        );
        $this->getConditionBuilder()->add("article.articleID IN (
            SELECT  articleID
            FROM    wcf1_article_content
            WHERE   articleContentID IN ({$subselect['sql']})
        )", $subselect['parameters']);
    }
}
