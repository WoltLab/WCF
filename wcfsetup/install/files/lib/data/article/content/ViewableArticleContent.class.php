<?php

namespace wcf\data\article\content;

use wcf\data\article\ViewableArticle;
use wcf\data\DatabaseObjectDecorator;

/**
 * Represents a viewable article content.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @mixin   ArticleContent
 * @extends DatabaseObjectDecorator<ArticleContent>
 */
class ViewableArticleContent extends DatabaseObjectDecorator
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = ArticleContent::class;

    /**
     * article object
     * @var ViewableArticle
     */
    protected $article;

    /**
     * Returns article object.
     *
     * @return  ViewableArticle
     */
    public function getArticle()
    {
        if ($this->article === null) {
            $this->article = new ViewableArticle($this->getDecoratedObject()->getArticle());
        }

        return $this->article;
    }

    /**
     * Sets the article objects.
     *
     * @return void
     */
    public function setArticle(ViewableArticle $article)
    {
        $this->article = $article;
    }

    /**
     * Returns a specific article content decorated as viewable article content.
     *
     * @return ?ViewableArticleContent
     */
    public static function getArticleContent(int $articleContentID)
    {
        $list = new ViewableArticleContentList();
        $list->setObjectIDs([$articleContentID]);
        $list->readObjects();

        return $list->search($articleContentID);
    }
}
