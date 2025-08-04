<?php

namespace wcf\event\article;

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
        public readonly \wcf\data\article\Article $article,
        public readonly \wcf\data\article\category\ArticleCategory $oldCategory,
        public readonly \wcf\data\article\category\ArticleCategory $newCategory,
    ) {
    }
}
