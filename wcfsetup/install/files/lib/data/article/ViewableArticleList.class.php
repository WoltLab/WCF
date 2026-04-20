<?php

namespace wcf\data\article;

use wcf\data\article\content\ViewableArticleContentList;
use wcf\system\WCF;

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

    #[\Override]
    public function readObjects()
    {
        parent::readObjects();

        // get article content
        if ($this->contentLoading && !empty($this->objectIDs)) {
            $contentList = new ViewableArticleContentList();
            $contentList->enableArticleLoading(false);
            $contentList->getConditionBuilder()->add('article_content.articleID IN (?)', [$this->objectIDs]);
            $contentList->getConditionBuilder()->add(
                '(article_content.languageID IS NULL OR article_content.languageID = ?)',
                [WCF::getLanguage()->languageID]
            );
            $contentList->readObjects();
            foreach ($contentList as $articleContent) {
                $article = $this->objects[$articleContent->articleID];
                $article->setArticleContent($articleContent);

                // Some providers do pre-populate internal caches in order to retrieve the data
                // for many objects in a single step.
                $article->getDiscussionProvider();
            }
        }
    }

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
