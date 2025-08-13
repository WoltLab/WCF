<?php

namespace wcf\event\article;

use wcf\data\article\Article;
use wcf\data\article\category\ArticleCategory;
use wcf\event\IPsr14Event;

/**
 * Indicates that the category for an article has been set.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class ArticleCategorySet implements IPsr14Event
{
    public function __construct(
        public readonly Article $article,
        public readonly ArticleCategory $oldCategory,
        public readonly ArticleCategory $newCategory,
    ) {
    }
}
