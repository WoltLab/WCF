<?php

namespace wcf\system\moderation;

use wcf\command\article\DeleteArticle;
use wcf\data\article\ArticleList;

/**
 * Permanently deletes trashed articles.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class ArticleTrashedContentPruner implements ITrashedContentPruner
{
    #[\Override]
    public function prune(int $deletedBefore, int $limit): void
    {
        $articleList = new ArticleList();
        $articleList->getConditionBuilder()->add('article.isDeleted = ?', [1]);
        $articleList->getConditionBuilder()->add('article.deleteTime < ?', [$deletedBefore]);
        $articleList->sqlLimit = $limit;
        $articleList->readObjects();

        foreach ($articleList as $article) {
            new DeleteArticle($article)();
        }
    }
}
