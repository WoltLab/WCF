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
     * @param int $objectID
     * @return  bool
     */
    public function canAdd($objectID);

    /**
     * Returns true if a comment requires approval.
     *
     * @param int $objectID
     * @return      bool
     */
    public function canAddWithoutApproval($objectID);

    /**
     * Returns true if the current user may edit given comment.
     *
     * @param Comment $comment
     * @return  bool
     */
    public function canEditComment(Comment $comment);

    /**
     * Returns true if the current user may edit given response.
     *
     * @param CommentResponse $response
     * @return  bool
     */
    public function canEditResponse(CommentResponse $response);

    /**
     * Returns true if the current user may delete given comment.
     *
     * @param Comment $comment
     * @return  bool
     */
    public function canDeleteComment(Comment $comment);

    /**
     * Returns true if the current user may delete given response.
     *
     * @param CommentResponse $response
     */
    public function canDeleteResponse(CommentResponse $response);

    /**
     * Returns true if the current user may moderated content identified by
     * object type id and object id.
     *
     * @param int $objectTypeID
     * @param int $objectID
     * @return  bool
     * @deprecated 6.1 use `ICommentPermissionManager::canModerateObject()` instead
     */
    public function canModerate($objectTypeID, $objectID);

    /**
     * Returns the amount of comments per page.
     *
     * @return  int
     */
    public function getCommentsPerPage();

    /**
     * Returns a link to the commented object with the given object type id and object id.
     *
     * @param int $objectTypeID
     * @param int $objectID
     * @return  string
     */
    public function getLink($objectTypeID, $objectID);

    /**
     * Returns the link to the given comment.
     *
     * @param Comment $comment
     * @return  string
     */
    public function getCommentLink(Comment $comment);

    /**
     * Returns the link to the given comment response.
     *
     * @param CommentResponse $response
     * @return  string
     */
    public function getResponseLink(CommentResponse $response);

    /**
     * Returns the title for a comment or response.
     *
     * @param int $objectTypeID
     * @param int $objectID
     * @param bool $isResponse
     * @return  string
     */
    public function getTitle($objectTypeID, $objectID, $isResponse = false);

    /**
     * Returns true if comments and responses for given object id are accessible
     * by current user.
     *
     * @param int $objectID
     * @param bool $validateWritePermission
     * @return  bool
     */
    public function isAccessible($objectID, $validateWritePermission = false);

    /**
     * Updates total count of comments (includes responses).
     *
     * @param int $objectID
     * @param int $value
     */
    public function updateCounter($objectID, $value);

    /**
     * Returns true if this comment type supports likes.
     *
     * @return  bool
     */
    public function supportsLike();

    /**
     * Returns true if this comment type supports reports.
     *
     * @return  bool
     */
    public function supportsReport();

    /**
     * Sets the list of disallowed bbcodes.
     */
    public function setDisallowedBBCodes();

    /**
     * Returns whether the given Comment or CommentResponse was created by
     * the content's author.
     *
     * @param Comment|CommentResponse $commentOrResponse
     * @return  bool
     */
    public function isContentAuthor($commentOrResponse);
}
