<?php
namespace wcf\data\comment\response;
use wcf\data\comment\Comment;
use wcf\data\comment\CommentEditor;
use wcf\data\comment\CommentList;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\bbcode\BBCodeHandler;
use wcf\system\comment\manager\ICommentManager;
use wcf\system\comment\CommentHandler;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\like\LikeHandler;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\WCF;
use wcf\util\MessageUtil;

/**
 * Executes comment response-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
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
	 * @var	Comment
	 */
	public $comment;
	
	/**
	 * comment manager object
	 * @var	ICommentManager
	 */
	public $commentManager;
	
	/**
	 * comment processor
	 * @var	ICommentManager
	 */
	protected $commentProcessor;
	
	/**
	 * @var HtmlInputProcessor
	 */
	protected $htmlInputProcessor;
	
	/**
	 * response object
	 * @var	CommentResponse
	 */
	protected $response;
	
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
		/** @var ICommentManager[] $processors */
		$processors = $responseIDs = $updateComments = [];
		foreach ($this->getObjects() as $response) {
			$objectTypeID = $comments[$response->commentID]->objectTypeID;
			
			if (!isset($processors[$objectTypeID])) {
				$objectType = ObjectTypeCache::getInstance()->getObjectType($objectTypeID);
				$processors[$objectTypeID] = $objectType->getProcessor();
				$responseIDs[$objectTypeID] = [];
			}
			$responseIDs[$objectTypeID][] = $response->responseID;
			
			if (!$ignoreCounters && !$response->isDisabled) {
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
				if (isset($updateComments[$comment->commentID])) {
					$commentEditor->updateCounters([
						'responses' => -1 * $updateComments[$comment->commentID]
					]);
				}
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
		$commentCanModerate = $this->commentManager->canModerate($this->comment->objectTypeID, $this->comment->objectID);
		
		// get response list
		$responseList = new StructuredCommentResponseList($this->commentManager, $this->comment);
		$responseList->getConditionBuilder()->add("comment_response.time > ?", [$this->parameters['data']['lastResponseTime']]);
		if (!$commentCanModerate) $responseList->getConditionBuilder()->add("comment_response.isDisabled = ?", [0]);
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
			'commentCanModerate' => $commentCanModerate,
			'likeData' => MODULE_LIKE ? $responseList->getLikeData() : [],
			'responseList' => $responseList,
			'commentManager' => $this->commentManager
		]);
		
		return [
			'commentID' => $this->comment->commentID,
			'lastResponseTime' => $lastResponseTime,
			'template' => WCF::getTPL()->fetch('commentResponseList')
		];
	}
	
	
	/**
	 * @inheritDoc
	 */
	public function validateBeginEdit() {
		$this->response = $this->getSingleObject();
		
		// validate object type id
		$objectType = $this->validateObjectType();
		
		// validate object id and permissions
		$this->commentProcessor = $objectType->getProcessor();
		if (!$this->commentProcessor->canEditResponse($this->response->getDecoratedObject())) {
			throw new PermissionDeniedException();
		}
		
		$this->setDisallowedBBCodes();
	}
	
	/**
	 * @inheritDoc
	 */
	public function beginEdit() {
		WCF::getTPL()->assign([
			'response' => $this->response,
			'wysiwygSelector' => 'commentResponseEditor'.$this->response->responseID
		]);
		
		return [
			'actionName' => 'beginEdit',
			'template' => WCF::getTPL()->fetch('commentResponseEditor', 'wcf')
		];
	}
	
	/**
	 * @inheritDoc
	 */
	public function validateSave() {
		$this->validateBeginEdit();
		
		$this->validateMessage();
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		/** @var HtmlInputProcessor $htmlInputProcessor */
		$htmlInputProcessor = $this->parameters['htmlInputProcessor'];
		
		$action = new CommentResponseAction([$this->response], 'update', [
			'data' => [
				'message' => $htmlInputProcessor->getHtml()
			]
		]);
		$action->executeAction();
		
		return [
			'actionName' => 'save',
			'message' => (new CommentResponse($this->response->responseID))->getFormattedMessage()
		];
	}
	
	/**
	 * Validates message parameter.
	 *
	 * @throws      UserInputException
	 */
	protected function validateMessage() {
		$this->readString('message', false, 'data');
		$this->parameters['data']['message'] = MessageUtil::stripCrap($this->parameters['data']['message']);
		
		if (empty($this->parameters['data']['message'])) {
			throw new UserInputException('message');
		}
		
		CommentHandler::enforceCensorship($this->parameters['data']['message']);
		
		$this->setDisallowedBBCodes();
		$htmlInputProcessor = $this->getHtmlInputProcessor($this->parameters['data']['message'], ($this->comment !== null ? $this->comment->commentID : 0));
		
		// search for disallowed bbcodes
		$disallowedBBCodes = $htmlInputProcessor->validate();
		if (!empty($disallowedBBCodes)) {
			throw new UserInputException('text', WCF::getLanguage()->getDynamicVariable('wcf.message.error.disallowedBBCodes', ['disallowedBBCodes' => $disallowedBBCodes]));
		}
		
		if ($htmlInputProcessor->appearsToBeEmpty()) {
			throw new UserInputException('message');
		}
		
		$this->parameters['htmlInputProcessor'] = $htmlInputProcessor;
	}
	
	/**
	 * Validates object type id parameter.
	 *
	 * @param       integer         $objectTypeID
	 * @return	ObjectType
	 * @throws	UserInputException
	 */
	protected function validateObjectType($objectTypeID = null) {
		if ($objectTypeID === null) {
			$this->readInteger('objectTypeID', false, 'data');
			$objectTypeID = $this->parameters['data']['objectTypeID'];
		}
		
		$objectType = ObjectTypeCache::getInstance()->getObjectType($objectTypeID);
		if ($objectType === null) {
			throw new UserInputException('objectTypeID');
		}
		
		return $objectType;
	}
	
	/**
	 * Sets the list of disallowed bbcodes for comments.
	 */
	protected function setDisallowedBBCodes() {
		BBCodeHandler::getInstance()->setDisallowedBBCodes(explode(',', WCF::getSession()->getPermission('user.comment.disallowedBBCodes')));
	}
	
	/**
	 * Returns the current html input processor or a new one if `$message` is not null.
	 *
	 * @param       string|null     $message        source message
	 * @param       integer         $objectID       object id
	 * @return      HtmlInputProcessor
	 */
	public function getHtmlInputProcessor($message = null, $objectID = 0) {
		if ($message === null) {
			return $this->htmlInputProcessor;
		}
		
		$this->htmlInputProcessor = new HtmlInputProcessor();
		$this->htmlInputProcessor->process($message, 'com.woltlab.wcf.comment', $objectID);
		
		return $this->htmlInputProcessor;
	}
}
