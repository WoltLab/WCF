<?php

namespace wcf\command\article;

use wcf\data\article\Article;
use wcf\data\article\ArticleEditor;
use wcf\event\article\ArticleUnpublished;
use wcf\system\event\EventHandler;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\user\notification\UserNotificationHandler;

/**
 * Unpublishes an article.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class UnpublishArticle
{
    public function __construct(public readonly Article $article)
    {
    }

    public function __invoke(): void
    {
        (new ArticleEditor($this->article))->update(['publicationStatus' => Article::UNPUBLISHED]);

        $this->deleteArticleActivity($this->article->articleID, $this->article->userID);

        $event = new ArticleUnpublished($this->article);
        EventHandler::getInstance()->fire($event);
    }

    private function deleteArticleActivity(int $articleID, int $userID): void
    {
        UserNotificationHandler::getInstance()->removeNotifications(
            'com.woltlab.wcf.article.notification',
            [$articleID]
        );

        UserActivityEventHandler::getInstance()->removeEvents(
            'com.woltlab.wcf.article.recentActivityEvent',
            [$articleID]
        );

        ArticleEditor::updateArticleCounter([
            $userID => -1,
        ]);
    }
}
