<?php

namespace wcf\data\article\content;

/**
 * Represents a list of article content as search results.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 *
 * @method  SearchResultArticleContent      current()
 * @method  SearchResultArticleContent[]        getObjects()
 * @method  SearchResultArticleContent|null     getSingleObject()
 * @method  SearchResultArticleContent|null     search($objectID)
 * @property    SearchResultArticleContent[] $objects
 */
class SearchResultArticleContentList extends ViewableArticleContentList
{
    /**
     * @inheritDoc
     */
    public $decoratorClassName = SearchResultArticleContent::class;
}
