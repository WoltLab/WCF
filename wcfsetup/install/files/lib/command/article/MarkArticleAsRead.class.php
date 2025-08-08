<?php

namespace wcf\command\article;

use wcf\data\article\Article;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\visitTracker\VisitTracker;
use wcf\system\WCF;

/**
 * Marks an article as read for the current logged in user.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class MarkArticleAsRead
{
    public function __construct(
        private readonly Article $article,
        private readonly int $visitTime = TIME_NOW
    ) {}

    public function __invoke(): void
    {
        VisitTracker::getInstance()->trackObjectVisit(
            'com.woltlab.wcf.article',
            $this->article->articleID,
            $this->visitTime
        );

        (new ResetUserStorageForUnreadArticles([WCF::getUser()->userID]))();

        $this->deleteObsoleteNotifications();
    }

    private function deleteObsoleteNotifications(): void
    {
        UserNotificationHandler::getInstance()->markAsConfirmed(
            'article',
            'com.woltlab.wcf.article.notification',
            [WCF::getUser()->userID],
            [$this->article->articleID],
        );
    }
}
