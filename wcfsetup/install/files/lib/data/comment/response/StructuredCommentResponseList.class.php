<?php
namespace wcf\data\comment\response;
use wcf\data\comment\Comment;
use wcf\data\like\object\LikeObject;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\comment\manager\ICommentManager;
use wcf\system\like\LikeHandler;

/**
 * Provides a structured comment response list.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.comment.response
 * @category	Community Framework
 *
 * @method	StructuredCommentResponse		current()
 * @method	StructuredCommentResponse[]		getObjects()
 * @method	StructuredCommentResponse|null		search($objectID)
 * @property	StructuredCommentResponse[]		$objects
 */
class StructuredCommentResponseList extends CommentResponseList {
	/**
	 * comment object
	 * @var	Comment;
	 */
	public $comment = null;
	
	/**
	 * comment manager
	 * @var	ICommentManager
	 */
	public $commentManager = null;
	
	/**
	 * minimum response time
	 * @var	integer
	 */
	public $minResponseTime = 0;
	
	/**
	 * @inheritDoc
	 */
	public $decoratorClassName = StructuredCommentResponse::class;
	
	/**
	 * @inheritDoc
	 */
	public $sqlLimit = 50;
	
	/**
	 * Creates a new structured comment response list.
	 * 
	 * @param	ICommentManager		$commentManager
	 * @param	Comment			$comment
	 */
	public function __construct(ICommentManager $commentManager, Comment $comment) {
		parent::__construct();
		
		$this->comment = $comment;
		$this->commentManager = $commentManager;
		
		$this->getConditionBuilder()->add("comment_response.commentID = ?", [$this->comment->commentID]);
		$this->sqlLimit = $this->commentManager->getCommentsPerPage();
	}
	
	/**
	 * @inheritDoc
	 */
	public function readObjects() {
		parent::readObjects();
		
		// get user ids
		$userIDs = [];
		foreach ($this->objects as $response) {
			if (!$this->minResponseTime || $response->time < $this->minResponseTime) $this->minResponseTime = $response->time;
			$userIDs[] = $response->userID;
			
			$response->setIsDeletable($this->commentManager->canDeleteResponse($response->getDecoratedObject()));
			$response->setIsEditable($this->commentManager->canEditResponse($response->getDecoratedObject()));
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
		
		$objectType = LikeHandler::getInstance()->getObjectType('com.woltlab.wcf.comment.response');
		LikeHandler::getInstance()->loadLikeObjects($objectType, $this->objectIDs);
		$likeData = ['response' => LikeHandler::getInstance()->getLikeObjects($objectType)];
		
		return $likeData;
	}
	
	/**
	 * Returns mimimum response time.
	 * 
	 * @return	integer
	 */
	public function getMinResponseTime() {
		return $this->minResponseTime;
	}
}
