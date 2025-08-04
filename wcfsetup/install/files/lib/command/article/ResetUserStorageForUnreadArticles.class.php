<?php

namespace wcf\command\article;

use wcf\system\user\storage\UserStorageHandler;

/**
 * Reset the user storage for unread articles.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class ResetUserStorageForUnreadArticles
{
    public function __construct(
        /** @var int[] */
        public readonly array $userIDs = []
    ) {
    }

    public function __invoke(): void
    {
        if ($this->userIDs === []) {
            UserStorageHandler::getInstance()->resetAll('unreadArticles');
            UserStorageHandler::getInstance()->resetAll('unreadWatchedArticles');
            UserStorageHandler::getInstance()->resetAll('unreadArticlesByCategory');
        } else {
            UserStorageHandler::getInstance()->reset($this->userIDs, 'unreadArticles');
            UserStorageHandler::getInstance()->reset($this->userIDs, 'unreadWatchedArticles');
            UserStorageHandler::getInstance()->reset($this->userIDs, 'unreadArticlesByCategory');
        }
    }
}
