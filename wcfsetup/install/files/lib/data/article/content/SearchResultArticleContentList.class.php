<?php

namespace wcf\data\article\content;

/**
 * Represents a list of article content as search results.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends ArticleContentList<SearchResultArticleContent>
 */
class SearchResultArticleContentList extends ArticleContentList
{
    /**
     * @inheritDoc
     */
    public $decoratorClassName = SearchResultArticleContent::class;
}
