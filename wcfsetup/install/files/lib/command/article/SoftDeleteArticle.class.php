<?php

namespace wcf\command\article;

use wcf\data\article\Article;
use wcf\data\article\ArticleEditor;
use wcf\event\article\ArticleSoftDeleted;
use wcf\system\event\EventHandler;

/**
 * Soft-deletes an article.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class SoftDeleteArticle
{
    public function __construct(private readonly Article $article) {}

    public function __invoke(): void
    {
        (new ArticleEditor($this->article))->update(['isDeleted' => 1]);

        (new ResetUserStorageForUnreadArticles())();

        $event = new ArticleSoftDeleted($this->article);
        EventHandler::getInstance()->fire($event);
    }
}
