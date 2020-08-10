<?php
namespace wcf\system\comment\manager;
use wcf\data\comment\response\CommentResponse;
use wcf\data\comment\Comment;
use wcf\data\DatabaseObjectDecorator;
use wcf\system\bbcode\BBCodeHandler;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Default implementation for comment managers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Comment\Manager
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
	 * permission name for comment/response creation without approval
	 * @var string
	 */
	protected $permissionAddWithoutModeration = '';
	
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
	 * permission name for the list of disallowed bbcodes
	 * @var string
	 */
	protected $permissionDisallowedBBCodes = 'user.comment.disallowedBBCodes';
	
	/**
	 * @inheritDoc
	 */
	public function canAdd($objectID) {
		if (VISITOR_USE_TINY_BUILD && !WCF::getUser()->userID) {
			return false;
		}
		
		if (!$this->isAccessible($objectID, true)) {
			return false;
		}
		
		return (WCF::getSession()->getPermission($this->permissionAdd) ? true : false);
	}
	
	/**
	 * @inheritDoc
	 */
	public function canAddWithoutApproval($objectID) {
		if (VISITOR_USE_TINY_BUILD && !WCF::getUser()->userID) {
			return false;
		}
		
		if (empty($this->permissionAddWithoutModeration)) {
			if (ENABLE_DEBUG_MODE) {
				throw new \RuntimeException("Missing permission name to create comments without approval.");
			}
			
			// backwards-compatibility in production mode
			return true;
		}
		
		return (WCF::getSession()->getPermission($this->permissionAddWithoutModeration) ? true : false);
	}
	
	/**
	 * @inheritDoc
	 */
	public function setDisallowedBBCodes() {
		BBCodeHandler::getInstance()->setDisallowedBBCodes(explode(',', WCF::getSession()->getPermission($this->permissionDisallowedBBCodes)));
	}
	
	/**
	 * @inheritDoc
	 */
	public function canEditComment(Comment $comment) {
		return $this->canEdit($comment->userID == WCF::getUser()->userID);
	}
	
	/**
	 * @inheritDoc
	 */
	public function canEditResponse(CommentResponse $response) {
		return $this->canEdit($response->userID == WCF::getUser()->userID);
	}
	
	/**
	 * @inheritDoc
	 */
	public function canDeleteComment(Comment $comment) {
		return $this->canDelete($comment->userID == WCF::getUser()->userID);
	}
	
	/**
	 * @inheritDoc
	 */
	public function canDeleteResponse(CommentResponse $response) {
		return $this->canDelete($response->userID == WCF::getUser()->userID);
	}
	
	/**
	 * @inheritDoc
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
	 * @inheritDoc
	 */
	public function getCommentsPerPage() {
		return $this->commentsPerPage;
	}
	
	/**
	 * @inheritDoc
	 */
	public function supportsLike() {
		return true;
	}
	
	/**
	 * @inheritDoc
	 */
	public function supportsReport() {
		return true;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getCommentLink(Comment $comment) {
		return $this->getLink($comment->objectTypeID, $comment->objectID) . '#comment' . $comment->commentID;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getResponseLink(CommentResponse $response) {
		return $this->getLink($response->getComment()->objectTypeID, $response->getComment()->objectID)
			. '#comment' . $response->commentID . '/response' . $response->responseID;
	}
	
	/**
	 * @inheritDoc
	 */
	public function isContentAuthor($commentOrResponse) {
		return false;
	}
	
	/**
	 * Returns the object ID for the given Comment or CommentResponse.
	 * 
	 * @return integer
	 */
	protected final function getObjectID($commentOrResponse) {
		if ($commentOrResponse instanceof CommentResponse || ($commentOrResponse instanceof DatabaseObjectDecorator && $commentOrResponse->getDecoratedObject() instanceof CommentResponse)) {
			return $commentOrResponse->getComment()->objectID;
		}
		else {
			return $commentOrResponse->objectID;
		}
	}
}
