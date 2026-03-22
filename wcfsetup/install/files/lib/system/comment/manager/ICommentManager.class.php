<?php

namespace wcf\system\comment\manager;

use wcf\data\comment\Comment;
use wcf\data\comment\response\CommentResponse;

/**
 * Default interface for comment managers.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
interface ICommentManager
{
    /**
     * Returns true if the current user may add comments or responses.
     *
     * @return bool
     */
    public function canAdd(int $objectID);

    /**
     * Returns true if a comment requires approval.
     *
     * @return bool
     */
    public function canAddWithoutApproval(int $objectID);

    /**
     * Returns true if the current user may edit given comment.
     *
     * @return bool
     */
    public function canEditComment(Comment $comment);

    /**
     * Returns true if the current user may edit given response.
     *
     * @return bool
     */
    public function canEditResponse(CommentResponse $response);

    /**
     * Returns true if the current user may delete given comment.
     *
     * @return bool
     */
    public function canDeleteComment(Comment $comment);

    /**
     * Returns true if the current user may delete given response.
     *
     * @return bool
     */
    public function canDeleteResponse(CommentResponse $response);

    /**
     * Returns true if the current user may moderated content identified by
     * object type id and object id.
     *
     * @return bool
     * @deprecated 6.1 use `ICommentPermissionManager::canModerateObject()` instead
     */
    public function canModerate(int $objectTypeID, int $objectID);

    /**
     * Returns the amount of comments per page.
     *
     * @return int
     */
    public function getCommentsPerPage();

    /**
     * Returns a link to the commented object with the given object type id and object id.
     *
     * @return string
     */
    public function getLink(int $objectTypeID, int $objectID);

    /**
     * Returns the link to the given comment.
     *
     * @return string
     */
    public function getCommentLink(Comment $comment);

    /**
     * Returns the link to the given comment response.
     *
     * @return string
     */
    public function getResponseLink(CommentResponse $response);

    /**
     * Returns the title for a comment or response.
     *
     * @return string
     */
    public function getTitle(int $objectTypeID, int $objectID, bool $isResponse = false);

    /**
     * Returns true if comments and responses for given object id are accessible
     * by current user.
     *
     * @return bool
     */
    public function isAccessible(int $objectID, bool $validateWritePermission = false);

    /**
     * Updates total count of comments (includes responses).
     *
     * @return void
     */
    public function updateCounter(int $objectID, int $value);

    /**
     * Returns true if this comment type supports likes.
     *
     * @return bool
     */
    public function supportsLike();

    /**
     * Returns true if this comment type supports reports.
     *
     * @return bool
     */
    public function supportsReport();

    /**
     * Sets the list of disallowed bbcodes.
     *
     * @return void
     */
    public function setDisallowedBBCodes();

    /**
     * Returns whether the given Comment or CommentResponse was created by
     * the content's author.
     *
     * @return bool
     */
    public function isContentAuthor(Comment|CommentResponse $commentOrResponse);
}
