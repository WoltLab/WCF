<?php
namespace wcf\system\comment\manager;
use wcf\data\comment\response\CommentResponse;
use wcf\data\comment\Comment;

/**
 * Default interface for comment managers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Comment\Manager
 */
interface ICommentManager {
	/**
	 * Returns true if the current user may add comments or responses.
	 * 
	 * @param	integer		$objectID
	 * @return	boolean
	 */
	public function canAdd($objectID);
	
	/**
	 * Returns true if a comment requires approval.
	 * 
	 * @param       integer         $objectID
	 * @return      boolean
	 */
	public function canAddWithoutApproval($objectID);
	
	/**
	 * Returns true if the current user may edit given comment.
	 * 
	 * @param	Comment		$comment
	 * @return	boolean
	 */
	public function canEditComment(Comment $comment);
	
	/**
	 * Returns true if the current user may edit given response.
	 * 
	 * @param	CommentResponse		$response
	 * @return	boolean
	 */
	public function canEditResponse(CommentResponse $response);
	
	/**
	 * Returns true if the current user may delete given comment.
	 * 
	 * @param	Comment		$comment
	 * @return	boolean
	 */
	public function canDeleteComment(Comment $comment);
	
	/**
	 * Returns true if the current user may delete given response.
	 * 
	 * @param	CommentResponse		$response
	 */
	public function canDeleteResponse(CommentResponse $response);
	
	/**
	 * Returns true if the current user may moderated content identified by
	 * object type id and object id.
	 * 
	 * @param	integer		$objectTypeID
	 * @param	integer		$objectID
	 * @return	boolean
	 */
	public function canModerate($objectTypeID, $objectID);
	
	/**
	 * Returns the amount of comments per page.
	 * 
	 * @return	integer
	 */
	public function getCommentsPerPage();
	
	/**
	 * Returns a link to the commented object with the given object type id and object id.
	 * 
	 * @param	integer		$objectTypeID
	 * @param	integer		$objectID
	 * @return	string
	 */
	public function getLink($objectTypeID, $objectID);
	
	/**
	 * Returns the link to the given comment.
	 *
	 * @param	Comment		$comment
	 * @return	string
	 */
	public function getCommentLink(Comment $comment);
	
	/**
	 * Returns the link to the given comment response.
	 *
	 * @param	CommentResponse		$response
	 * @return	string
	 */
	public function getResponseLink(CommentResponse $response);
	
	/**
	 * Returns the title for a comment or response.
	 * 
	 * @param	integer		$objectTypeID
	 * @param	integer		$objectID
	 * @param	boolean		$isResponse
	 * @return	string
	 */
	public function getTitle($objectTypeID, $objectID, $isResponse = false);
	
	/**
	 * Returns true if comments and responses for given object id are accessible
	 * by current user.
	 * 
	 * @param	integer		$objectID
	 * @param	boolean		$validateWritePermission
	 * @return	boolean
	 */
	public function isAccessible($objectID, $validateWritePermission = false);
	
	/**
	 * Updates total count of comments (includes responses).
	 * 
	 * @param	integer		$objectID
	 * @param	integer		$value
	 */
	public function updateCounter($objectID, $value);
	
	/**
	 * Returns true if this comment type supports likes.
	 * 
	 * @return	boolean
	 */
	public function supportsLike();
	
	/**
	 * Returns true if this comment type supports reports.
	 * 
	 * @return	boolean
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
	 * @param	Comment|CommentResponse	$commentOrResponse
	 * @return	boolean
	 */
	public function isContentAuthor($commentOrResponse);
}
