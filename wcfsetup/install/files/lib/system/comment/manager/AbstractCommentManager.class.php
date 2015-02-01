<?php
namespace wcf\system\comment\manager;
use wcf\data\comment\response\CommentResponse;
use wcf\data\comment\Comment;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Default implementation for comment managers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.comment.manager
 * @category	Community Framework
 */
abstract class AbstractCommentManager extends SingletonFactory implements ICommentManager {
	/**
	 * display comments per page
	 * @var	integer
	 */
	public $commentsPerPage = 30;
	
	/**
	 * permission name for comment/response creation
	 * @var	string
	 */
	protected $permissionAdd = '';
	
	/**
	 * permission name for comment/response moderation
	 * @var	string
	 */
	protected $permissionCanModerate = '';
	
	/**
	 * permission name for deletion of own comments/responses
	 * @var	string
	 */
	protected $permissionDelete = '';
	
	/**
	 * permission name for editing of own comments/responses
	 * @var	string
	 */
	protected $permissionEdit = '';
	
	/**
	 * permission name for deletion of comments/responses (moderator)
	 * @var	string
	 */
	protected $permissionModDelete = '';
	
	/**
	 * permission name for editing of comments/responses (moderator)
	 * @var	string
	 */
	protected $permissionModEdit = '';
	
	/**
	 * @see	\wcf\system\comment\manager\ICommentManager::canAdd()
	 */
	public function canAdd($objectID) {
		if (!$this->isAccessible($objectID, true)) {
			return false;
		}
		
		return (WCF::getSession()->getPermission($this->permissionAdd) ? true : false);
	}
	
	/**
	 * @see	\wcf\system\comment\manager\ICommentManager::canEditComment()
	 */
	public function canEditComment(Comment $comment) {
		return $this->canEdit(($comment->userID == WCF::getUser()->userID));
	}
	
	/**
	 * @see	\wcf\system\comment\manager\ICommentManager::canEditResponse()
	 */
	public function canEditResponse(CommentResponse $response) {
		return $this->canEdit(($response->userID == WCF::getUser()->userID));
	}
	
	/**
	 * @see	\wcf\system\comment\manager\ICommentManager::canDeleteComment()
	 */
	public function canDeleteComment(Comment $comment) {
		return $this->canDelete(($comment->userID == WCF::getUser()->userID));
	}
	
	/**
	 * @see	\wcf\system\comment\manager\ICommentManager::canDeleteResponse()
	 */
	public function canDeleteResponse(CommentResponse $response) {
		return $this->canDelete(($response->userID == WCF::getUser()->userID));
	}
	
	/**
	 * @see	\wcf\system\comment\manager\ICommentManager::canModerate()
	 */
	public function canModerate($objectTypeID, $objectID) {
		return (WCF::getSession()->getPermission($this->permissionCanModerate) ? true : false);
	}
	
	/**
	 * Returns true if the current user may edit a comment/response.
	 * 
	 * @param	boolean		$isOwner
	 * @return	boolean
	 */
	protected function canEdit($isOwner) {
		// disallow guests
		if (!WCF::getUser()->userID) {
			return false;
		}
		
		// check moderator permission
		if (WCF::getSession()->getPermission($this->permissionModEdit)) {
			return true;
		}
		
		// check user permission and ownership
		if ($isOwner && WCF::getSession()->getPermission($this->permissionEdit)) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Returns true if the current user may delete a comment/response.
	 * 
	 * @param	boolean		$isOwner
	 * @return	boolean
	 */
	protected function canDelete($isOwner) {
		// disallow guests
		if (!WCF::getUser()->userID) {
			return false;
		}
		
		// check moderator permission
		if (WCF::getSession()->getPermission($this->permissionModDelete)) {
			return true;
		}
		
		// check user permission and ownership
		if ($isOwner && WCF::getSession()->getPermission($this->permissionDelete)) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * @see	\wcf\system\comment\manager\ICommentManager::getCommentsPerPage()
	 */
	public function getCommentsPerPage() {
		return $this->commentsPerPage;
	}
	
	/**
	 * @see	\wcf\system\comment\manager\ICommentManager::supportsLike()
	 */
	public function supportsLike() {
		return true;
	}
	
	/**
	 * @see	\wcf\system\comment\manager\ICommentManager::supportsReport()
	 */
	public function supportsReport() {
		return true;
	}
}
