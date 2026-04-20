<?php

namespace wcf\system\cache\runtime;

use wcf\data\article\ArticleList;
use wcf\data\article\Article;

/**
 * Runtime cache implementation for articles.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends AbstractRuntimeCache<Article, ArticleList>
 */
class ArticleRuntimeCache extends AbstractRuntimeCache
{
    /**
     * @inheritDoc
     */
    protected $listClassName = ArticleList::class;
}
