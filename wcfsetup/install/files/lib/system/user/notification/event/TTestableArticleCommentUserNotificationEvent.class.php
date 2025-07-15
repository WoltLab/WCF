<?php

namespace wcf\system\user\notification\event;

use wcf\data\article\category\ArticleCategory;
use wcf\data\user\UserProfile;
use wcf\system\comment\CommentHandler;

/**
 * Provides a default implementation of
 *  `TTestableCommentUserNotificationEvent::getTestCommentObjectData()`
 * used for article comment-related and article comment response-related user notification
 * events.
 *
 * @author  Marcel Werk
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.5
 */
trait TTestableArticleCommentUserNotificationEvent
{
    use TTestableArticleUserNotificationEvent;
    use TTestableCategorizedUserNotificationEvent;

    /**
     * @return array{objectID: int, objectTypeID: ?int}
     * @see TTestableCommentUserNotificationEvent::createTestComment()
     */
    protected static function getTestCommentObjectData(UserProfile $recipient, UserProfile $author)
    {
        return [
            'objectID' => self::getTestArticle(self::createTestCategory(ArticleCategory::OBJECT_TYPE_NAME), $author)
                ->getArticleContent()
                ->articleContentID,
            'objectTypeID' => CommentHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.articleComment'),
        ];
    }
}
