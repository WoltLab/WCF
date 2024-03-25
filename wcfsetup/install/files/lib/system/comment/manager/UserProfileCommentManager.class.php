<?php

namespace wcf\system\comment\manager;

use wcf\data\comment\Comment;
use wcf\data\comment\response\CommentResponse;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\ignore\UserIgnore;
use wcf\data\user\UserProfile;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\cache\runtime\UserRuntimeCache;
use wcf\system\cache\runtime\ViewableCommentResponseRuntimeCache;
use wcf\system\cache\runtime\ViewableCommentRuntimeCache;
use wcf\system\like\IViewableLikeProvider;
use wcf\system\WCF;

/**
 * User profile comment manager implementation.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserProfileCommentManager extends AbstractCommentManager implements
    IViewableLikeProvider,
    ICommentPermissionManager
{
    /**
     * @inheritDoc
     */
    protected $permissionAdd = 'user.profileComment.canAddComment';

    /**
     * @inheritDoc
     */
    protected $permissionAddWithoutModeration = 'user.profileComment.canAddCommentWithoutModeration';

    /**
     * @inheritDoc
     */
    protected $permissionCanModerate = 'mod.profileComment.canModerateComment';

    /**
     * @inheritDoc
     */
    protected $permissionDelete = 'user.profileComment.canDeleteComment';

    /**
     * @inheritDoc
     */
    protected $permissionEdit = 'user.profileComment.canEditComment';

    /**
     * @inheritDoc
     */
    protected $permissionModDelete = 'mod.profileComment.canDeleteComment';

    /**
     * @inheritDoc
     */
    protected $permissionModEdit = 'mod.profileComment.canEditComment';

    /**
     * @inheritDoc
     */
    public function isAccessible($objectID, $validateWritePermission = false)
    {
        // check object id
        $userProfile = UserProfileRuntimeCache::getInstance()->getObject($objectID);
        if ($userProfile === null) {
            return false;
        }

        // check visibility
        if ($userProfile->isProtected()) {
            return false;
        }

        // check target user settings
        if ($validateWritePermission) {
            if (
                !$userProfile->isAccessible('canWriteProfileComments')
                && $userProfile->userID != WCF::getUser()->userID
            ) {
                return false;
            }

            if ($userProfile->isIgnoredUser(WCF::getUser()->userID, UserIgnore::TYPE_BLOCK_DIRECT_CONTACT)) {
                return false;
            }
        }

        return true;
    }

    #[\Override]
    public function canModerateObject(int $objectTypeID, int $objectID, UserProfile $user): bool
    {
        $userProfile = UserProfileRuntimeCache::getInstance()->getObject($objectID);
        if ($userProfile === null) {
            return false;
        }

        /** @see UserProfile::isProtected() */
        if (
            !(
                $user->getPermission('admin.general.canViewPrivateUserOptions')
            || $userProfile->isAccessible('canViewProfile', $user->userID)
            || $userProfile->userID === $user->userID
            )
        ) {
            return false;
        }

        return (bool)$user->getPermission($this->permissionCanModerate);
    }

    /**
     * @inheritDoc
     */
    public function getLink($objectTypeID, $objectID)
    {
        $user = UserRuntimeCache::getInstance()->getObject($objectID);
        if ($user) {
            return $user->getLink();
        }

        return '';
    }

    /**
     * @inheritDoc
     */
    public function getCommentLink(Comment $comment)
    {
        return $this->getLink($comment->objectTypeID, $comment->objectID) . '#wall/comment' . $comment->commentID;
    }

    /**
     * @inheritDoc
     */
    public function getResponseLink(CommentResponse $response)
    {
        return $this->getLink($response->getComment()->objectTypeID, $response->getComment()->objectID)
            . '#wall/comment' . $response->commentID . '/response' . $response->responseID;
    }

    /**
     * @inheritDoc
     */
    public function getTitle($objectTypeID, $objectID, $isResponse = false)
    {
        if ($isResponse) {
            return WCF::getLanguage()->get('wcf.user.profile.content.wall.commentResponse');
        }

        return WCF::getLanguage()->getDynamicVariable('wcf.user.profile.content.wall.comment');
    }

    /**
     * @inheritDoc
     */
    public function updateCounter($objectID, $value)
    {
        // does nothing
    }

    /**
     * @inheritDoc
     */
    public function canDeleteComment(Comment $comment)
    {
        if (
            $comment->objectID == WCF::getUser()->userID
            && WCF::getSession()->getPermission('user.profileComment.canDeleteCommentInOwnProfile')
        ) {
            return true;
        }

        return parent::canDeleteComment($comment);
    }

    /**
     * @inheritDoc
     */
    public function canDeleteResponse(CommentResponse $response)
    {
        if (
            $response->getComment()->objectID == WCF::getUser()->userID
            && WCF::getSession()->getPermission('user.profileComment.canDeleteCommentInOwnProfile')
        ) {
            return true;
        }

        return parent::canDeleteResponse($response);
    }

    /**
     * @inheritDoc
     */
    public function prepare(array $likes)
    {
        if (!WCF::getSession()->getPermission('user.profile.canViewUserProfile')) {
            return;
        }

        $commentLikeObjectType = ObjectTypeCache::getInstance()
            ->getObjectTypeByName('com.woltlab.wcf.like.likeableObject', 'com.woltlab.wcf.comment');

        $commentIDs = $responseIDs = [];
        foreach ($likes as $like) {
            if ($like->objectTypeID == $commentLikeObjectType->objectTypeID) {
                $commentIDs[] = $like->objectID;
            } else {
                $responseIDs[] = $like->objectID;
            }
        }

        // fetch response
        $userIDs = $responses = [];
        if (!empty($responseIDs)) {
            $responses = ViewableCommentResponseRuntimeCache::getInstance()->getObjects($responseIDs);

            foreach ($responses as $response) {
                $commentIDs[] = $response->commentID;
                if ($response->userID) {
                    $userIDs[] = $response->userID;
                }
            }
        }

        // fetch comments
        $comments = ViewableCommentRuntimeCache::getInstance()->getObjects($commentIDs);

        // fetch users
        $users = [];
        foreach ($comments as $comment) {
            $userIDs[] = $comment->objectID;
            if ($comment->userID) {
                $userIDs[] = $comment->userID;
            }
        }
        if (!empty($userIDs)) {
            $users = UserProfileRuntimeCache::getInstance()->getObjects(\array_unique($userIDs));
        }

        // set message
        foreach ($likes as $like) {
            if ($like->objectTypeID == $commentLikeObjectType->objectTypeID) {
                // comment like
                if (isset($comments[$like->objectID])) {
                    $comment = $comments[$like->objectID];

                    if (isset($users[$comment->objectID]) && !$users[$comment->objectID]->isProtected()) {
                        $like->setIsAccessible();

                        // short output
                        $text = WCF::getLanguage()->getDynamicVariable(
                            'wcf.like.title.com.woltlab.wcf.user.profileComment',
                            [
                                'commentAuthor' => $comment->userID ? $users[$comment->userID] : null,
                                'comment' => $comment,
                                'user' => $users[$comment->objectID],
                                'reaction' => $like,
                                // @deprecated 5.3 Use `$reaction` instead
                                'like' => $like,
                            ]
                        );
                        $like->setTitle($text);

                        // output
                        $like->setDescription($comment->getExcerpt());
                    }
                }
            } else {
                // response like
                if (isset($responses[$like->objectID])) {
                    $response = $responses[$like->objectID];
                    $comment = $comments[$response->commentID];

                    if (isset($users[$comment->objectID]) && !$users[$comment->objectID]->isProtected()) {
                        $like->setIsAccessible();

                        // short output
                        $text = WCF::getLanguage()->getDynamicVariable(
                            'wcf.like.title.com.woltlab.wcf.user.profileComment.response',
                            [
                                'responseAuthor' => $response->userID ? $users[$response->userID] : null,
                                'commentAuthor' => $comment->userID ? $users[$comment->userID] : null,
                                'user' => $users[$comment->objectID],
                                'reaction' => $like,
                                // @deprecated 5.3 Use `$reaction` instead
                                'like' => $like,
                                'response' => $response,
                            ]
                        );
                        $like->setTitle($text);

                        // output
                        $like->setDescription($response->getExcerpt());
                    }
                }
            }
        }
    }
}
