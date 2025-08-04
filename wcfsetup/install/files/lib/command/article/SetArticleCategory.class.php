<?php

namespace wcf\command\article;

use wcf\data\article\Article;
use wcf\data\article\ArticleEditor;
use wcf\data\article\category\ArticleCategory;
use wcf\event\article\ArticleCategorySet;
use wcf\system\event\EventHandler;

/**
 * Sets the category for an article.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class SetArticleCategory
{
    public function __construct(
        public readonly Article $article,
        public readonly ArticleCategory $category,
    ) {
    }

    public function __invoke(): void
    {
        (new ArticleEditor($this->article))->update(['categoryID' => $this->category->categoryID]);

        $event = new ArticleCategorySet($this->article, $this->article->getCategory(), $this->category);
        EventHandler::getInstance()->fire($event);
    }
}
