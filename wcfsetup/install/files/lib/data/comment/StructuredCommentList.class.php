<?php
namespace wcf\data\comment;
use wcf\data\comment\response\CommentResponseList;
use wcf\data\comment\response\StructuredCommentResponse;
use wcf\data\like\object\LikeObject;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\comment\manager\ICommentManager;
use wcf\system\like\LikeHandler;

/**
 * Provides a structured comment list fetching last responses for every comment.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Comment
 *
 * @method	StructuredComment		current()
 * @method	StructuredComment[]		getObjects()
 * @method	StructuredComment|null		search($objectID)
 * @property	StructuredComment[]		$objects
 */
class StructuredCommentList extends CommentList {
	/**
	 * comment manager object
	 * @var	ICommentManager
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
	 * @var	integer[]
	 */
	public $responseIDs = [];
	
	/**
	 * @inheritDoc
	 */
	public $decoratorClassName = StructuredComment::class;
	
	/**
	 * @inheritDoc
	 */
	public $sqlLimit = 30;
	
	/**
	 * @inheritDoc
	 */
	public $sqlOrderBy = 'comment.time DESC';
	
	/**
	 * Creates a new structured comment list.
	 * 
	 * @param	ICommentManager		$commentManager
	 * @param	integer			$objectTypeID
	 * @param	integer			$objectID
	 */
	public function __construct(ICommentManager $commentManager, $objectTypeID, $objectID) {
		parent::__construct();
		
		$this->commentManager = $commentManager;
		$this->objectTypeID = $objectTypeID;
		$this->objectID = $objectID;
		
		$this->getConditionBuilder()->add("comment.objectTypeID = ?", [$objectTypeID]);
		$this->getConditionBuilder()->add("comment.objectID = ?", [$objectID]);
		$this->sqlLimit = $this->commentManager->getCommentsPerPage();
	}
	
	/**
	 * @inheritDoc
	 */
	public function readObjects() {
		parent::readObjects();
		
		// fetch response ids
		$responseIDs = $userIDs = [];
		foreach ($this->objects as $comment) {
			if (!$this->minCommentTime || $comment->time < $this->minCommentTime) $this->minCommentTime = $comment->time;
			$commentResponseIDs = $comment->getResponseIDs();
			foreach ($commentResponseIDs as $responseID) {
				$this->responseIDs[] = $responseID;
				$responseIDs[$responseID] = $comment->commentID;
			}
			
			if ($comment->userID) {
				$userIDs[] = $comment->userID;
			}
			
			$comment->setIsDeletable($this->commentManager->canDeleteComment($comment->getDecoratedObject()));
			$comment->setIsEditable($this->commentManager->canEditComment($comment->getDecoratedObject()));
		}
		
		// fetch last responses
		if (!empty($responseIDs)) {
			$responseList = new CommentResponseList();
			$responseList->setObjectIDs(array_keys($responseIDs));
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
		
		// cache user ids
		if (!empty($userIDs)) {
			UserProfileRuntimeCache::getInstance()->cacheObjectIDs(array_unique($userIDs));
		}
	}
	
	/**
	 * Fetches the like data.
	 * 
	 * @return	LikeObject[][]
	 */
	public function getLikeData() {
		if (empty($this->objectIDs)) return [];
		
		$likeData = [];
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
	 * @return	ICommentManager
	 */
	public function getCommentManager() {
		return $this->commentManager;
	}
}
