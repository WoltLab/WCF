<?php
namespace wcf\system\comment\manager;
use wcf\data\comment\response\CommentResponse;
use wcf\data\comment\Comment;

/**
 * Default interface for comment managers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
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
	 * Returns true if the current user may edit given comment.
	 * 
	 * @param	\wcf\data\comment\Comment	$comment
	 * @return	boolean
	 */
	public function canEditComment(Comment $comment);
	
	/**
	 * Returns true if the current user may edit given response.
	 * 
	 * @param	\wcf\data\comment\response\CommentResponse	$response
	 * @return	boolean
	 */
	public function canEditResponse(CommentResponse $response);
	
	/**
	 * Returns true if the current user may delete given comment.
	 * 
	 * @param	\wcf\data\comment\Comment	$comment
	 * @return	boolean
	 */
	public function canDeleteComment(Comment $comment);
	
	/**
	 * Returns true if the current user may delete given response.
	 * 
	 * @param	\wcf\data\comment\response\CommentResponse	$response
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
	 * Returns a link to given object type id and object id.
	 * 
	 * @param	integer		$objectTypeID
	 * @param	integer		$objectID
	 * @return	string
	 */
	public function getLink($objectTypeID, $objectID);
	
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
}
