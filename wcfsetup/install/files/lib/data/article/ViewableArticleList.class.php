<?php

namespace wcf\data\article;

/**
 * Represents a list of articles.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends ArticleList<ViewableArticle>
 */
class ViewableArticleList extends ArticleList
{
    /**
     * @inheritDoc
     */
    public $decoratorClassName = ViewableArticle::class;

    /**
     * enables/disables the loading of article content objects
     * @var bool
     */
    protected $contentLoading = true;

    /**
     * enables/disables the loading of embedded objects in the article contents
     * @var bool
     * @since   5.4
     */
    protected $embeddedObjectLoading = true;

    /**
     * Enables/disables the loading of article content objects.
     *
     * @return void
     */
    public function enableContentLoading(bool $enable = true)
    {
        $this->contentLoading = $enable;
    }

    /**
     * Enables/disables the loading of embedded objects in the article contents.
     *
     * @since   5.4
     */
    public function enableEmbeddedObjectLoading(bool $enable = true): void
    {
        $this->embeddedObjectLoading = $enable;
    }
}
