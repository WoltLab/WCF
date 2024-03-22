<?php

namespace wcf\system\comment\manager;

use wcf\data\object\type\ObjectTypeCache;
use wcf\data\page\Page;
use wcf\data\page\PageList;
use wcf\data\user\UserProfile;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\cache\runtime\ViewableCommentResponseRuntimeCache;
use wcf\system\cache\runtime\ViewableCommentRuntimeCache;
use wcf\system\like\IViewableLikeProvider;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Page comment manager implementation.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class PageCommentManager extends AbstractCommentManager implements IViewableLikeProvider
{
    /**
     * @inheritDoc
     */
    protected $permissionAdd = 'user.page.canAddComment';

    /**
     * @inheritDoc
     */
    protected $permissionAddWithoutModeration = 'user.page.canAddCommentWithoutModeration';

    /**
     * @inheritDoc
     */
    protected $permissionDelete = 'user.page.canDeleteComment';

    /**
     * @inheritDoc
     */
    protected $permissionEdit = 'user.page.canEditComment';

    /**
     * @inheritDoc
     */
    protected $permissionModDelete = 'mod.page.canDeleteComment';

    /**
     * @inheritDoc
     */
    protected $permissionModEdit = 'mod.page.canEditComment';

    /**
     * @inheritDoc
     */
    protected $permissionCanModerate = 'mod.page.canModerateComment';

    /**
     * @inheritDoc
     */
    public function isAccessible($objectID, $validateWritePermission = false)
    {
        // check object id
        $page = new Page($objectID);
        if (!$page->pageID || !$page->isAccessible()) {
            return false;
        }

        return true;
    }

    #[\Override]
    public function canViewObject(int $objectID, UserProfile $user): bool
    {
        $page = new Page($objectID);
        if (!$page->pageID) {
            return false;
        }
        return $page->isAccessible($user->getDecoratedObject());
    }

    #[\Override]
    public function canWriteComments(int $objectID, UserProfile $user): bool
    {
        return $this->canViewObject($objectID, $user);
    }


    /**
     * @inheritDoc
     */
    public function getLink($objectTypeID, $objectID)
    {
        return LinkHandler::getInstance()->getCmsLink($objectID);
    }

    /**
     * @inheritDoc
     */
    public function getTitle($objectTypeID, $objectID, $isResponse = false)
    {
        if ($isResponse) {
            return WCF::getLanguage()->get('wcf.page.commentResponse');
        }

        return WCF::getLanguage()->getDynamicVariable('wcf.page.comment');
    }

    /**
     * @inheritDoc
     */
    public function updateCounter($objectID, $value)
    {
    }

    /**
     * @inheritDoc
     */
    public function prepare(array $likes)
    {
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
        $pageIDs = [];
        foreach ($comments as $comment) {
            $pageIDs[] = $comment->objectID;
            if ($comment->userID) {
                $userIDs[] = $comment->userID;
            }
        }
        if (!empty($userIDs)) {
            $users = UserProfileRuntimeCache::getInstance()->getObjects(\array_unique($userIDs));
        }

        // fetch pages
        $pages = [];
        if (!empty($pageIDs)) {
            $pageList = new PageList();
            $pageList->setObjectIDs($pageIDs);
            $pageList->readObjects();
            $pages = $pageList->getObjects();
        }

        // set message
        foreach ($likes as $like) {
            if ($like->objectTypeID == $commentLikeObjectType->objectTypeID) {
                // comment like
                if (isset($comments[$like->objectID])) {
                    $comment = $comments[$like->objectID];

                    if (isset($pages[$comment->objectID]) && $pages[$comment->objectID]->isAccessible()) {
                        $like->setIsAccessible();

                        // short output
                        $text = WCF::getLanguage()->getDynamicVariable('wcf.like.title.com.woltlab.wcf.pageComment', [
                            'commentAuthor' => $comment->userID ? $users[$comment->userID] : null,
                            'comment' => $comment,
                            'page' => $pages[$comment->objectID],
                            'reaction' => $like,
                            // @deprecated 5.3 Use `$reaction` instead
                            'like' => $like,
                        ]);
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

                    if (isset($pages[$comment->objectID]) && $pages[$comment->objectID]->isAccessible()) {
                        $like->setIsAccessible();

                        // short output
                        $text = WCF::getLanguage()->getDynamicVariable(
                            'wcf.like.title.com.woltlab.wcf.pageComment.response',
                            [
                                'responseAuthor' => $comment->userID ? $users[$response->userID] : null,
                                'commentAuthor' => $comment->userID ? $users[$comment->userID] : null,
                                'page' => $pages[$comment->objectID],
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
