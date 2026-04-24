<?php

namespace wcf\system\cache\runtime;

use wcf\data\article\ViewableArticle;
use wcf\data\article\ViewableArticleList;

/**
 * Runtime cache implementation for viewable articles.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated 6.3 Use `ArticleRuntimeCache` instead.
 *
 * @extends AbstractRuntimeCache<ViewableArticle, ViewableArticleList>
 */
class ViewableArticleRuntimeCache extends AbstractRuntimeCache
{
    /**
     * @inheritDoc
     */
    protected $listClassName = ViewableArticleList::class;
}
