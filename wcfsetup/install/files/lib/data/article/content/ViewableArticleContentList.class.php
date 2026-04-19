<?php

namespace wcf\data\article\content;

use wcf\data\article\ViewableArticle;
use wcf\data\article\ViewableArticleList;

/**
 * Represents a list of viewable article contents.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
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
     * enables/disables the loading of embedded objects in the article contents
     * @var bool
     * @since   5.4
     */
    protected $embeddedObjectLoading = true;

    /**
     * @since 6.2
     */
    protected bool $articleLoading = true;

    #[\Override]
    public function readObjects()
    {
        parent::readObjects();

        $articleIDs = [];
        foreach ($this->getObjects() as $articleContent) {
            $articleIDs[] = $articleContent->articleID;
        }

        /** @var array<int, ViewableArticle> */
        $articles = [];
        if ($this->articleLoading && $articleIDs !== []) {
            $articleList = new ViewableArticleList();
            // Prevents an infinite loop, because the list would load the
            // content itself.
            $articleList->enableContentLoading(false);
            $articleList->setObjectIDs($articleIDs);
            $articleList->readObjects();
            $articles = $articleList->getObjects();
        }

        foreach ($this->getObjects() as $articleContent) {
            if ($this->articleLoading) {
                $article = $articles[$articleContent->articleID] ?? null;
                if ($article === null) {
                    throw new \LogicException('Unable to find article with id "' . $articleContent->articleID . '".');
                }

                $articleContent->setArticle($article);
            }
        }
    }

    /**
     * Enables/disables the loading of embedded objects in the article contents.
     *
     * @since   5.4
     * @deprecated 6.3
     */
    public function enableEmbeddedObjectLoading(bool $enable = true): void
    {
        $this->embeddedObjectLoading = $enable;
    }

    /**
     * @since 6.2
     */
    public function enableArticleLoading(bool $enable = true): void
    {
        $this->articleLoading = $enable;
    }
}
