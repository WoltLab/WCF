<?php

namespace wcf\system\listView\user;

use wcf\data\article\AccessibleArticleList;
use wcf\system\tagging\TagEngine;

/**
 * List view for the list of articles filtered by tags.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class TaggedArticleListView extends ArticleListView
{
    public function __construct(
        /** @var list<int> */
        public readonly array $tagIDs,
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function createObjectList(): AccessibleArticleList
    {
        $list = parent::createObjectList();

        $subselect = TagEngine::getInstance()->getSubselectForObjectsByTagIDs(
            'com.woltlab.wcf.article',
            $this->tagIDs
        );
        $list->getConditionBuilder()->add("article.articleID IN (
            SELECT  articleID
            FROM    wcf1_article_content
            WHERE   articleContentID IN ({$subselect['sql']})
        )", $subselect['parameters']);

        return $list;
    }

    #[\Override]
    public function getParameters(): array
    {
        return ['tagIDs' => $this->tagIDs];
    }
}
