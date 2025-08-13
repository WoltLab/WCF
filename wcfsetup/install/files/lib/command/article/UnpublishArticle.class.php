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
    public function __construct(private readonly Article $article) {}

    public function __invoke(): void
    {
        (new ArticleEditor($this->article))->update(['publicationStatus' => Article::UNPUBLISHED]);

        $this->removeNotifications($this->article->articleID);
        $this->removeUserActivity($this->article->articleID);

        ArticleEditor::updateArticleCounter([
            $this->article->userID => -1,
        ]);

        $event = new ArticleUnpublished($this->article);
        EventHandler::getInstance()->fire($event);
    }

    private function removeNotifications(int $articleID): void
    {
        UserNotificationHandler::getInstance()->removeNotifications(
            'com.woltlab.wcf.article.notification',
            [$articleID]
        );
    }

    private function removeUserActivity(int $articleID): void
    {
        UserActivityEventHandler::getInstance()->removeEvents(
            'com.woltlab.wcf.article.recentActivityEvent',
            [$articleID]
        );
    }
}
