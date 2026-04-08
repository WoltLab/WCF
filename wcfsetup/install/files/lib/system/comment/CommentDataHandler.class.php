<?php

namespace wcf\system\comment;

use wcf\data\comment\Comment;
use wcf\data\user\UserProfile;
use wcf\system\cache\runtime\CommentRuntimeCache;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\SingletonFactory;

/**
 * Handles common data resources for comment-related user notifications
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated  3.0, use CommentRuntimeCache and UserProfileRuntimeCache
 */
class CommentDataHandler extends SingletonFactory
{
    /**
     * @return void
     */
    public function cacheCommentID(int $commentID)
    {
        CommentRuntimeCache::getInstance()->cacheObjectID($commentID);
    }

    /**
     * @return void
     */
    public function cacheUserID(int $userID)
    {
        UserProfileRuntimeCache::getInstance()->cacheObjectID($userID);
    }

    /**
     * @return ?Comment
     */
    public function getComment(int $commentID)
    {
        return CommentRuntimeCache::getInstance()->getObject($commentID);
    }

    /**
     * @return ?UserProfile
     */
    public function getUser(int $userID)
    {
        return UserProfileRuntimeCache::getInstance()->getObject($userID);
    }
}
