<?php

namespace wcf\system\comment\manager;

use wcf\data\comment\Comment;
use wcf\data\comment\response\CommentResponse;
use wcf\data\DatabaseObjectDecorator;
use wcf\system\bbcode\BBCodeHandler;
use wcf\system\WCF;

/**
 * Default implementation for comment managers.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
abstract class AbstractCommentManager implements ICommentManager
{
    /**
     * display comments per page
     * @var int
     */
    public $commentsPerPage = 30;

    /**
     * permission name for comment/response creation
     * @var string
     */
    protected $permissionAdd = '';

    /**
     * permission name for comment/response creation without approval
     * @var string
     */
    protected $permissionAddWithoutModeration = '';

    /**
     * permission name for comment/response moderation
     * @var string
     */
    protected $permissionCanModerate = '';

    /**
     * permission name for deletion of own comments/responses
     * @var string
     */
    protected $permissionDelete = '';

    /**
     * permission name for editing of own comments/responses
     * @var string
     */
    protected $permissionEdit = '';

    /**
     * permission name for deletion of comments/responses (moderator)
     * @var string
     */
    protected $permissionModDelete = '';

    /**
     * permission name for editing of comments/responses (moderator)
     * @var string
     */
    protected $permissionModEdit = '';

    /**
     * permission name for the list of disallowed bbcodes
     * @var string
     */
    protected $permissionDisallowedBBCodes = 'user.comment.disallowedBBCodes';

    #[\Override]
    public function canAdd(int $objectID)
    {
        if (\VISITOR_USE_TINY_BUILD && WCF::getUser()->isGuest()) {
            return false;
        }

        if (!$this->isAccessible($objectID, true)) {
            return false;
        }

        return WCF::getSession()->hasPermission($this->permissionAdd);
    }

    #[\Override]
    public function canAddWithoutApproval(int $objectID)
    {
        if (\VISITOR_USE_TINY_BUILD && WCF::getUser()->isGuest()) {
            return false;
        }

        if (empty($this->permissionAddWithoutModeration)) {
            if (\ENABLE_DEBUG_MODE) {
                throw new \RuntimeException("Missing permission name to create comments without approval.");
            }

            // backwards-compatibility in production mode
            return true;
        }

        return WCF::getSession()->hasPermission($this->permissionAddWithoutModeration);
    }

    #[\Override]
    public function setDisallowedBBCodes()
    {
        BBCodeHandler::getInstance()->setDisallowedBBCodes(\explode(
            ',',
            WCF::getSession()->getPermission($this->permissionDisallowedBBCodes)
        ));
    }

    #[\Override]
    public function canEditComment(Comment $comment)
    {
        return $this->canEdit($comment->userID === WCF::getUser()->userID);
    }

    #[\Override]
    public function canEditResponse(CommentResponse $response)
    {
        return $this->canEdit($response->userID === WCF::getUser()->userID);
    }

    #[\Override]
    public function canDeleteComment(Comment $comment)
    {
        return $this->canDelete($comment->userID === WCF::getUser()->userID);
    }

    #[\Override]
    public function canDeleteResponse(CommentResponse $response)
    {
        return $this->canDelete($response->userID === WCF::getUser()->userID);
    }

    #[\Override]
    public function canModerate(int $objectTypeID, int $objectID)
    {
        return WCF::getSession()->hasPermission($this->permissionCanModerate);
    }

    /**
     * Returns true if the current user may edit a comment/response.
     *
     * @return  bool
     */
    protected function canEdit(bool $isOwner)
    {
        // disallow guests
        if (WCF::getUser()->isGuest()) {
            return false;
        }

        // check moderator permission
        if (WCF::getSession()->hasPermission($this->permissionModEdit)) {
            return true;
        }

        // check user permission and ownership
        if ($isOwner && WCF::getSession()->hasPermission($this->permissionEdit)) {
            return true;
        }

        return false;
    }

    /**
     * Returns true if the current user may delete a comment/response.
     *
     * @return  bool
     */
    protected function canDelete(bool $isOwner)
    {
        // disallow guests
        if (WCF::getUser()->isGuest()) {
            return false;
        }

        // check moderator permission
        if (WCF::getSession()->hasPermission($this->permissionModDelete)) {
            return true;
        }

        // check user permission and ownership
        if ($isOwner && WCF::getSession()->hasPermission($this->permissionDelete)) {
            return true;
        }

        return false;
    }

    #[\Override]
    public function getCommentsPerPage()
    {
        return $this->commentsPerPage;
    }

    #[\Override]
    public function supportsLike()
    {
        return true;
    }

    #[\Override]
    public function supportsReport()
    {
        return true;
    }

    #[\Override]
    public function getCommentLink(Comment $comment)
    {
        return $this->getLink($comment->objectTypeID, $comment->objectID) . '#comment' . $comment->commentID;
    }

    #[\Override]
    public function getResponseLink(CommentResponse $response)
    {
        return $this->getLink($response->getComment()->objectTypeID, $response->getComment()->objectID)
            . '#comment' . $response->commentID . '/response' . $response->responseID;
    }

    #[\Override]
    public function isContentAuthor(Comment|CommentResponse $commentOrResponse)
    {
        return false;
    }

    /**
     * Returns the object ID for the given Comment or CommentResponse.
     *
     * @param Comment|CommentResponse|DatabaseObjectDecorator<CommentResponse> $commentOrResponse
     * @return int
     */
    final protected function getObjectID(Comment|CommentResponse|DatabaseObjectDecorator $commentOrResponse)
    {
        if (
            $commentOrResponse instanceof CommentResponse
            || (
                $commentOrResponse instanceof DatabaseObjectDecorator
                // @phpstan-ignore instanceof.alwaysTrue
                && $commentOrResponse->getDecoratedObject() instanceof CommentResponse
            )
        ) {
            return $commentOrResponse->getComment()->objectID;
        }

        return $commentOrResponse->objectID;
    }
}
