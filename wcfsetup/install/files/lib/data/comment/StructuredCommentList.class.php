<?php
namespace wcf\data\comment;
use wcf\data\comment\response\CommentResponseList;
use wcf\data\comment\response\StructuredCommentResponse;
use wcf\data\user\UserProfile;
use wcf\system\comment\manager\ICommentManager;
use wcf\system\like\LikeHandler;

/**
 * Provides a structured comment list fetching last responses for every comment.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.comment
 * @category	Community Framework
 */
class StructuredCommentList extends CommentList {
	/**
	 * comment manager object
	 * @var	\wcf\system\comment\manager\ICommentManager
	 */
	public $commentManager = null;
	
	/**
	 * minimum comment time
	 * @var	integer
	 */
	public $minCommentTime = 0;
	
	/**
	 * object type id
	 * @var	integer
	 */
	public $objectTypeID = 0;
	
	/**
	 * object id
	 * @var	integer
	 */
	public $objectID = 0;
	
	/**
	 * ids of the responses of the comments in the list
	 * @var	array<integer>
	 */
	public $responseIDs = array();
	
	/**
	 * @see	\wcf\data\DatabaseObjectList::$sqlLimit
	 */
	public $sqlLimit = 30;
	
	/**
	 * @see	\wcf\data\DatabaseObjectList::$sqlOrderBy
	 */
	public $sqlOrderBy = 'comment.time DESC';
	
	/**
	 * Creates a new structured comment list.
	 * 
	 * @param	\wcf\system\comment\manager\ICommentManager	$commentManager
	 * @param	integer						$objectTypeID
	 * @param	integer						$objectID
	 */
	public function __construct(ICommentManager $commentManager, $objectTypeID, $objectID) {
		parent::__construct();
		
		$this->commentManager = $commentManager;
		$this->objectTypeID = $objectTypeID;
		$this->objectID = $objectID;
		
		$this->getConditionBuilder()->add("comment.objectTypeID = ?", array($objectTypeID));
		$this->getConditionBuilder()->add("comment.objectID = ?", array($objectID));
		$this->sqlLimit = $this->commentManager->getCommentsPerPage();
	}
	
	/**
	 * @see	\wcf\data\DatabaseObjectList::readObjects()
	 */
	public function readObjects() {
		parent::readObjects();
		
		// fetch response ids
		$responseIDs = $userIDs = array();
		foreach ($this->objects as &$comment) {
			if (!$this->minCommentTime || $comment->time < $this->minCommentTime) $this->minCommentTime = $comment->time;
			$commentResponseIDs = $comment->getResponseIDs();
			foreach ($commentResponseIDs as $responseID) {
				$this->responseIDs[] = $responseID;
				$responseIDs[$responseID] = $comment->commentID;
			}
			
			if ($comment->userID) {
				$userIDs[] = $comment->userID;
			}
			
			$comment = new StructuredComment($comment);
			$comment->setIsDeletable($this->commentManager->canDeleteComment($comment->getDecoratedObject()));
			$comment->setIsEditable($this->commentManager->canEditComment($comment->getDecoratedObject()));
		}
		unset($comment);
		
		// fetch last responses
		if (!empty($responseIDs)) {
			$responseList = new CommentResponseList();
			$responseList->getConditionBuilder()->add("comment_response.responseID IN (?)", array(array_keys($responseIDs)));
			$responseList->readObjects();
			
			foreach ($responseList as $response) {
				$response = new StructuredCommentResponse($response);
				$response->setIsDeletable($this->commentManager->canDeleteResponse($response->getDecoratedObject()));
				$response->setIsEditable($this->commentManager->canEditResponse($response->getDecoratedObject()));
				
				$commentID = $responseIDs[$response->responseID];
				$this->objects[$commentID]->addResponse($response);
				
				if ($response->userID) {
					$userIDs[] = $response->userID;
				}
			}
		}
		
		// fetch user data and avatars
		if (!empty($userIDs)) {
			$userIDs = array_unique($userIDs);
			
			$users = UserProfile::getUserProfiles($userIDs);
			foreach ($this->objects as $comment) {
				if ($comment->userID && isset($users[$comment->userID])) {
					$comment->setUserProfile($users[$comment->userID]);
				}
				
				foreach ($comment as $response) {
					if ($response->userID && isset($users[$response->userID])) {
						$response->setUserProfile($users[$response->userID]);
					}
				}
			}
		}
	}
	
	/**
	 * Fetches the like data.
	 * 
	 * @return	array
	 */
	public function getLikeData() {
		if (empty($this->objectIDs)) return array();
		
		$likeData = array();
		$commentObjectType = LikeHandler::getInstance()->getObjectType('com.woltlab.wcf.comment');
		LikeHandler::getInstance()->loadLikeObjects($commentObjectType, $this->getObjectIDs());
		$likeData['comment'] = LikeHandler::getInstance()->getLikeObjects($commentObjectType);
		
		if (!empty($this->responseIDs)) {
			$responseObjectType = LikeHandler::getInstance()->getObjectType('com.woltlab.wcf.comment.response');
			LikeHandler::getInstance()->loadLikeObjects($responseObjectType, $this->responseIDs);
			$likeData['response'] = LikeHandler::getInstance()->getLikeObjects($responseObjectType);
		}
		
		return $likeData;
	}
	
	/**
	 * Returns minimum comment time.
	 * 
	 * @return	integer
	 */
	public function getMinCommentTime() {
		return $this->minCommentTime;
	}
	
	/**
	 * Returns the comment manager object.
	 * 
	 * @return	\wcf\system\comment\manager\ICommentManager
	 */
	public function getCommentManager() {
		return $this->commentManager;
	}
}
