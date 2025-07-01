<?php

namespace wcf\system\tagging;

use wcf\system\listView\user\ArticleListView;
use wcf\system\listView\user\TaggedArticleListView;

/**
 * Implementation of ITaggedListViewProvider for tagging of cms articles.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       3.0
 *
 * @extends AbstractTaggedListViewProvider<ArticleListView>
 */
final class TaggableArticle extends AbstractTaggedListViewProvider
{
    #[\Override]
    public function getListView(array $tagIDs): ArticleListView
    {
        return new TaggedArticleListView($tagIDs);
    }
}
