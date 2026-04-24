<?php

namespace wcf\system\cache\runtime;

use wcf\data\article\content\ArticleContent;
use wcf\data\article\content\ArticleContentList;

/**
 * Runtime cache implementation for article contents.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends AbstractRuntimeCache<ArticleContent, ArticleContentList>
 */
class ArticleContentRuntimeCache extends AbstractRuntimeCache
{
    /**
     * @inheritDoc
     */
    protected $listClassName = ArticleContentList::class;
}
