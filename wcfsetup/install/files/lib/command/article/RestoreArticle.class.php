<?php

namespace wcf\command\article;

use wcf\data\article\Article;
use wcf\data\article\ArticleEditor;
use wcf\event\article\ArticleRestored;
use wcf\system\event\EventHandler;

/**
 * Restores a soft-deleted article.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class RestoreArticle
{
    public function __construct(private readonly Article $article) {}

    public function __invoke(): void
    {
        (new ArticleEditor($this->article))->update(['isDeleted' => 0]);

        (new ResetUserStorageForUnreadArticles())();

        $event = new ArticleRestored($this->article);
        EventHandler::getInstance()->fire($event);
    }
}
