<?php

namespace wcf\system\comment\manager;

use wcf\data\comment\Comment;
use wcf\data\comment\response\CommentResponse;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\ignore\UserIgnore;
use wcf\data\user\UserProfile;
use wcf\system\cache\runtime\CommentResponseRuntimeCache;
use wcf\system\cache\runtime\CommentRuntimeCache;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\cache\runtime\UserRuntimeCache;
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

    #[\Override]
    public function isAccessible(int $objectID, bool $validateWritePermission = false)
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
                && $userProfile->userID !== WCF::getUser()->userID
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
                $user->hasPermission('admin.general.canViewPrivateUserOptions')
                || $userProfile->isAccessible('canViewProfile', $user->userID)
                || $userProfile->userID === $user->userID
            )
        ) {
            return false;
        }

        return $user->hasPermission($this->permissionCanModerate);
    }

    #[\Override]
    public function getLink(int $objectTypeID, int $objectID)
    {
        $user = UserRuntimeCache::getInstance()->getObject($objectID);
        if ($user !== null) {
            return $user->getLink();
        }

        return '';
    }

    #[\Override]
    public function getCommentLink(Comment $comment)
    {
        return $this->getLink($comment->objectTypeID, $comment->objectID) . '#wall/comment' . $comment->commentID;
    }

    #[\Override]
    public function getResponseLink(CommentResponse $response)
    {
        return $this->getLink($response->getComment()->objectTypeID, $response->getComment()->objectID)
            . '#wall/comment' . $response->commentID . '/response' . $response->responseID;
    }

    #[\Override]
    public function getTitle(int $objectTypeID, int $objectID, bool $isResponse = false)
    {
        if ($isResponse) {
            return WCF::getLanguage()->get('wcf.user.profile.content.wall.commentResponse');
        }

        return WCF::getLanguage()->getDynamicVariable('wcf.user.profile.content.wall.comment');
    }

    #[\Override]
    public function updateCounter(int $objectID, int $value)
    {
        // does nothing
    }

    #[\Override]
    public function canDeleteComment(Comment $comment)
    {
        if (
            $comment->objectID === WCF::getUser()->userID
            && WCF::getSession()->hasPermission('user.profileComment.canDeleteCommentInOwnProfile')
        ) {
            return true;
        }

        return parent::canDeleteComment($comment);
    }

    #[\Override]
    public function canDeleteResponse(CommentResponse $response)
    {
        if (
            $response->getComment()->objectID === WCF::getUser()->userID
            && WCF::getSession()->hasPermission('user.profileComment.canDeleteCommentInOwnProfile')
        ) {
            return true;
        }

        return parent::canDeleteResponse($response);
    }

    #[\Override]
    public function prepare(array $likes)
    {
        if (!WCF::getSession()->hasPermission('user.profile.canViewUserProfile')) {
            return;
        }

        $commentLikeObjectType = ObjectTypeCache::getInstance()
            ->getObjectTypeByName('com.woltlab.wcf.like.likeableObject', 'com.woltlab.wcf.comment');

        $commentIDs = $responseIDs = [];
        foreach ($likes as $like) {
            if ($like->objectTypeID === $commentLikeObjectType->objectTypeID) {
                $commentIDs[] = $like->objectID;
            } else {
                $responseIDs[] = $like->objectID;
            }
        }

        // fetch response
        $responses = [];
        if ($responseIDs !== []) {
            $responses = CommentResponseRuntimeCache::getInstance()->getObjects($responseIDs);

            foreach ($responses as $response) {
                $commentIDs[] = $response->commentID;
            }
        }

        // fetch comments
        $comments = CommentRuntimeCache::getInstance()->getObjects($commentIDs);

        // fetch users
        $users = $userIDs = [];
        foreach ($comments as $comment) {
            $userIDs[] = $comment->objectID;
        }
        if ($userIDs !== []) {
            $users = UserProfileRuntimeCache::getInstance()->getObjects(\array_unique($userIDs));
        }

        // set message
        foreach ($likes as $like) {
            if ($like->objectTypeID === $commentLikeObjectType->objectTypeID) {
                // comment like
                if (isset($comments[$like->objectID])) {
                    $comment = $comments[$like->objectID];

                    if (isset($users[$comment->objectID]) && !$users[$comment->objectID]->isProtected()) {
                        $like->setIsAccessible();

                        $like->setTitle(WCF::getLanguage()->getDynamicVariable(
                            'wcf.like.title.com.woltlab.wcf.user.profileComment',
                            [
                                'commentAuthor' => $comment->userID !== null ? $comment->getUserProfile() : null,
                                'comment' => $comment,
                                'user' => $users[$comment->objectID],
                                'reaction' => $like,
                                'author' => $like->getUserProfile(),
                            ]
                        ));
                        $like->setLink($comment->getLink());
                        $like->setDescription(\strip_tags($comment->getExcerpt()));
                    }
                }
            } else {
                // response like
                if (isset($responses[$like->objectID])) {
                    $response = $responses[$like->objectID];
                    $comment = $comments[$response->commentID];

                    if (isset($users[$comment->objectID]) && !$users[$comment->objectID]->isProtected()) {
                        $like->setIsAccessible();

                        $like->setTitle(WCF::getLanguage()->getDynamicVariable(
                            'wcf.like.title.com.woltlab.wcf.user.profileComment.response',
                            [
                                'responseAuthor' => $response->userID !== null ? $response->getUserProfile() : null,
                                'commentAuthor' => $comment->userID !== null ? $comment->getUserProfile() : null,
                                'user' => $users[$comment->objectID],
                                'reaction' => $like,
                                'author' => $like->getUserProfile(),
                            ]
                        ));
                        $like->setLink($response->getLink());
                        $like->setDescription(\strip_tags($response->getExcerpt()));
                    }
                }
            }
        }
    }
}
