<?php

namespace wcf\command\article;

use wcf\system\visitTracker\VisitTracker;
use wcf\system\WCF;

/**
 * Marks all articles as read for the current logged in user.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class MarkAllArticleAsRead
{
    public function __construct()
    {
    }

    public function __invoke(): void
    {
        if (!WCF::getUser()->userID) {
            return;
        }

        VisitTracker::getInstance()->trackTypeVisit('com.woltlab.wcf.article');

        (new ResetUserStorageForUnreadArticles([WCF::getUser()->userID]))();
    }
}
