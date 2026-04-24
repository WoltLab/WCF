<?php

namespace wcf\data\article\content;

/**
 * Represents a list of viewable article contents.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated 6.3 Use `ArticleContentList` instead.
 *
 * @extends ArticleContentList<ViewableArticleContent>
 */
class ViewableArticleContentList extends ArticleContentList
{
    /**
     * @inheritDoc
     */
    public $decoratorClassName = ViewableArticleContent::class;

    /**
     * Enables/disables the loading of embedded objects in the article contents.
     *
     * @since   5.4
     */
    public function enableEmbeddedObjectLoading(bool $enable = true): void {}

    /**
     * @since 6.2
     */
    public function enableArticleLoading(bool $enable = true): void {}
}
