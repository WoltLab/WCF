<?php
namespace wcf\data\comment\response;
use wcf\data\comment\Comment;
use wcf\data\comment\CommentEditor;
use wcf\data\comment\CommentList;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\like\LikeHandler;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\WCF;

/**
 * Executes comment response-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Comment\Response
 * 
 * @method	CommentResponse			create()
 * @method	CommentResponseEditor[]		getObjects()
 * @method	CommentResponseEditor		getSingleObject()
 */
class CommentResponseAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $allowGuestAccess = ['loadResponses'];
	
	/**
	 * @inheritDoc
	 */
	protected $className = CommentResponseEditor::class;
	
	/**
	 * comment object
	 * @var	\wcf\data\comment\Comment
	 */
	public $comment = null;
	
	/**
	 * comment manager object
	 * @var	\wcf\system\comment\manager\ICommentManager
	 */
	public $commentManager = null;
	
	/**
	 * @inheritDoc
	 */
	public function delete() {
		if (empty($this->objects)) {
			$this->readObjects();
		}
		
		if (empty($this->objects)) {
			return 0;
		}
		
		$ignoreCounters = !empty($this->parameters['ignoreCounters']);
		
		// read object type ids for comments
		$commentIDs = [];
		foreach ($this->getObjects() as $response) {
			$commentIDs[] = $response->commentID;
		}
		
		$commentList = new CommentList();
		$commentList->setObjectIDs($commentIDs);
		$commentList->readObjects();
		$comments = $commentList->getObjects();
		
		// update counters
		$processors = $responseIDs = $updateComments = [];
		foreach ($this->getObjects() as $response) {
			$objectTypeID = $comments[$response->commentID]->objectTypeID;
			
			if (!isset($processors[$objectTypeID])) {
				$objectType = ObjectTypeCache::getInstance()->getObjectType($objectTypeID);
				$processors[$objectTypeID] = $objectType->getProcessor();
				$responseIDs[$objectTypeID] = [];
			}
			$responseIDs[$objectTypeID][] = $response->responseID;
			
			if (!$ignoreCounters) {
				$processors[$objectTypeID]->updateCounter($comments[$response->commentID]->objectID, -1);
				
				if (!isset($updateComments[$response->commentID])) {
					$updateComments[$response->commentID] = 0;
				}
				
				$updateComments[$response->commentID]++;
			}
		}
		
		// remove responses
		$count = parent::delete();
		
		// update comment responses and cached response ids
		if (!$ignoreCounters) {
			foreach ($comments as $comment) {
				$commentEditor = new CommentEditor($comment);
				$commentEditor->updateResponseIDs();
				$commentEditor->updateCounters([
					'responses' => -1 * $updateComments[$comment->commentID]
				]);
			}
		}
		
		$likeObjectIDs = [];
		$notificationObjectTypes = [];
		foreach ($responseIDs as $objectTypeID => $objectIDs) {
			// remove activity events
			$objectType = ObjectTypeCache::getInstance()->getObjectType($objectTypeID);
			if (UserActivityEventHandler::getInstance()->getObjectTypeID($objectType->objectType.'.response.recentActivityEvent')) {
				UserActivityEventHandler::getInstance()->removeEvents($objectType->objectType.'.response.recentActivityEvent', $objectIDs);
			}
			
			// delete notifications
			if (UserNotificationHandler::getInstance()->getObjectTypeID($objectType->objectType.'.response.notification')) {
				UserNotificationHandler::getInstance()->removeNotifications($objectType->objectType.'.response.notification', $objectIDs);
			}
			
			$likeObjectIDs = array_merge($likeObjectIDs, $objectIDs);
			
			if (UserNotificationHandler::getInstance()->getObjectTypeID($objectType->objectType.'.response.like.notification')) {
				$notificationObjectTypes[] = $objectType->objectType.'.response.like.notification';
			}
		}
		
		// remove likes
		if (!empty($likeObjectIDs)) {
			LikeHandler::getInstance()->removeLikes('com.woltlab.wcf.comment.response', $likeObjectIDs, $notificationObjectTypes);
		}
		
		return $count;
	}
	
	/**
	 * Validates parameters to load responses for a given comment id.
	 */
	public function validateLoadResponses() {
		$this->readInteger('commentID', false, 'data');
		$this->readInteger('lastResponseTime', false, 'data');
		$this->readBoolean('loadAllResponses', true, 'data');
		
		$this->comment = new Comment($this->parameters['data']['commentID']);
		if (!$this->comment->commentID) {
			throw new UserInputException('commentID');
		}
		
		$this->commentManager = ObjectTypeCache::getInstance()->getObjectType($this->comment->objectTypeID)->getProcessor();
		if (!$this->commentManager->isAccessible($this->comment->objectID)) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * Returns parsed responses for given comment id.
	 * 
	 * @return	array
	 */
	public function loadResponses() {
		// get response list
		$responseList = new StructuredCommentResponseList($this->commentManager, $this->comment);
		$responseList->getConditionBuilder()->add("comment_response.time > ?", [$this->parameters['data']['lastResponseTime']]);
		if (!$this->parameters['data']['loadAllResponses']) $responseList->sqlLimit = 50;
		$responseList->readObjects();
		
		$lastResponseTime = 0;
		foreach ($responseList as $response) {
			if (!$lastResponseTime) {
				$lastResponseTime = $response->time;
			}
			
			$lastResponseTime = max($lastResponseTime, $response->time);
		}
		
		WCF::getTPL()->assign([
			'likeData' => (MODULE_LIKE ? $responseList->getLikeData() : []),
			'responseList' => $responseList,
			'commentManager' => $this->commentManager
		]);
		
		return [
			'commentID' => $this->comment->commentID,
			'lastResponseTime' => $lastResponseTime,
			'template' => WCF::getTPL()->fetch('commentResponseList')
		];
	}
}
