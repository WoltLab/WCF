<?php
namespace wcf\data\comment;
use wcf\data\comment\response\CommentResponse;
use wcf\data\comment\response\CommentResponseAction;
use wcf\data\comment\response\CommentResponseEditor;
use wcf\data\comment\response\CommentResponseList;
use wcf\data\comment\response\StructuredCommentResponse;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\captcha\CaptchaHandler;
use wcf\system\comment\manager\ICommentManager;
use wcf\system\comment\CommentHandler;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\like\LikeHandler;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\user\notification\object\type\IMultiRecipientCommentUserNotificationObjectType;
use wcf\system\user\notification\object\CommentResponseUserNotificationObject;
use wcf\system\user\notification\object\CommentUserNotificationObject;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\WCF;
use wcf\util\MessageUtil;
use wcf\util\UserUtil;

/**
 * Executes comment-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Comment
 * 
 * @method	Comment			create()
 * @method	CommentEditor[]		getObjects()
 * @method	CommentEditor		getSingleObject()
 */
class CommentAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $allowGuestAccess = ['addComment', 'addResponse', 'loadComments', 'getGuestDialog'];
	
	/**
	 * captcha object type used for comments
	 * @var	ObjectType
	 */
	public $captchaObjectType = null;
	
	/**
	 * @inheritDoc
	 */
	protected $className = CommentEditor::class;
	
	/**
	 * comment object
	 * @var	Comment
	 */
	protected $comment = null;
	
	/**
	 * comment processor
	 * @var	ICommentManager
	 */
	protected $commentProcessor = null;
	
	/**
	 * response object
	 * @var	CommentResponse
	 */
	protected $response = null;
	
	/**
	 * comment object created by addComment()
	 * @var	Comment
	 */
	public $createdComment = null;
	
	/**
	 * response object created by addResponse()
	 * @var	CommentResponse
	 */
	public $createdResponse = null;
	
	/**
	 * errors occuring durch the validation of addComment or addResponse
	 * @var	array
	 */
	public $validationErrors = [];
	
	/**
	 * @inheritDoc
	 */
	public function delete() {
		if (empty($this->objects)) {
			$this->readObjects();
		}
		
		// update counters
		$processors = [];
		$groupCommentIDs = $commentIDs = [];
		foreach ($this->getObjects() as $comment) {
			if (!isset($processors[$comment->objectTypeID])) {
				$objectType = ObjectTypeCache::getInstance()->getObjectType($comment->objectTypeID);
				$processors[$comment->objectTypeID] = $objectType->getProcessor();
				
				$groupCommentIDs[$comment->objectTypeID] = [];
			}
			
			$processors[$comment->objectTypeID]->updateCounter($comment->objectID, -1 * ($comment->responses + 1));
			$groupCommentIDs[$comment->objectTypeID][] = $comment->commentID;
			$commentIDs[] = $comment->commentID;
		}
		
		if (!empty($groupCommentIDs)) {
			$likeObjectIDs = [];
			$notificationObjectTypes = [];
			foreach ($groupCommentIDs as $objectTypeID => $objectIDs) {
				// remove activity events
				$objectType = ObjectTypeCache::getInstance()->getObjectType($objectTypeID);
				if (UserActivityEventHandler::getInstance()->getObjectTypeID($objectType->objectType.'.recentActivityEvent')) {
					UserActivityEventHandler::getInstance()->removeEvents($objectType->objectType.'.recentActivityEvent', $objectIDs);
				}
				
				$likeObjectIDs = array_merge($likeObjectIDs, $objectIDs);
				
				// delete notifications
				$objectType = ObjectTypeCache::getInstance()->getObjectType($objectTypeID);
				if (UserNotificationHandler::getInstance()->getObjectTypeID($objectType->objectType.'.notification')) {
					UserNotificationHandler::getInstance()->removeNotifications($objectType->objectType.'.notification', $objectIDs);
				}
				
				if (UserNotificationHandler::getInstance()->getObjectTypeID($objectType->objectType.'.like.notification')) {
					$notificationObjectTypes[] = $objectType->objectType.'.like.notification';
				}
			}
			
			// remove likes
			LikeHandler::getInstance()->removeLikes('com.woltlab.wcf.comment', $likeObjectIDs, $notificationObjectTypes);
		}
		
		// delete responses
		if (!empty($commentIDs)) {
			$commentResponseList = new CommentResponseList();
			$commentResponseList->getConditionBuilder()->add('comment_response.commentID IN (?)', [$commentIDs]);
			$commentResponseList->readObjectIDs();
			if (count($commentResponseList->getObjectIDs())) {
				$action = new CommentResponseAction($commentResponseList->getObjectIDs(), 'delete', [
					'ignoreCounters' => true
				]);
				$action->executeAction();
			}
		}
		
		return parent::delete();
	}
	
	/**
	 * Validates parameters to load comments.
	 */
	public function validateLoadComments() {
		$this->readInteger('lastCommentTime', false, 'data');
		$this->readInteger('objectID', false, 'data');
		
		$objectType = $this->validateObjectType();
		$this->commentProcessor = $objectType->getProcessor();
		if (!$this->commentProcessor->isAccessible($this->parameters['data']['objectID'])) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * Returns parsed comments.
	 * 
	 * @return	array
	 */
	public function loadComments() {
		$commentList = CommentHandler::getInstance()->getCommentList($this->commentProcessor, $this->parameters['data']['objectTypeID'], $this->parameters['data']['objectID'], false);
		$commentList->getConditionBuilder()->add("comment.time < ?", [$this->parameters['data']['lastCommentTime']]);
		$commentList->readObjects();
		
		WCF::getTPL()->assign([
			'commentList' => $commentList,
			'likeData' => (MODULE_LIKE ? $commentList->getLikeData() : [])
		]);
		
		return [
			'lastCommentTime' => $commentList->getMinCommentTime(),
			'template' => WCF::getTPL()->fetch('commentList')
		];
	}
	
	/**
	 * Validates parameters to add a comment.
	 */
	public function validateAddComment() {
		CommentHandler::enforceFloodControl();
		
		$this->readInteger('objectID', false, 'data');
		
		$this->validateUsername();
		$this->validateCaptcha();
		
		$this->validateMessage();
		$objectType = $this->validateObjectType();
		
		// validate object id and permissions
		$this->commentProcessor = $objectType->getProcessor();
		if (!$this->commentProcessor->canAdd($this->parameters['data']['objectID'])) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * Adds a comment.
	 * 
	 * @return	string[]
	 */
	public function addComment() {
		if (!empty($this->validationErrors)) {
			if (!empty($this->parameters['data']['username'])) {
				WCF::getSession()->register('username', $this->parameters['data']['username']);
			}
			WCF::getTPL()->assign('errorType', $this->validationErrors);
			
			$guestDialog = $this->getGuestDialog();
			return [
				'useCaptcha' => $guestDialog['useCaptcha'],
				'guestDialog' => $guestDialog['template']
			];
		}
		
		// create comment
		$this->createdComment = CommentEditor::create([
			'objectTypeID' => $this->parameters['data']['objectTypeID'],
			'objectID' => $this->parameters['data']['objectID'],
			'time' => TIME_NOW,
			'userID' => WCF::getUser()->userID ?: null,
			'username' => WCF::getUser()->userID ? WCF::getUser()->username : $this->parameters['data']['username'],
			'message' => $this->parameters['data']['message'],
			'responses' => 0,
			'responseIDs' => serialize([])
		]);
		
		// update counter
		$this->commentProcessor->updateCounter($this->parameters['data']['objectID'], 1);
		
		// fire activity event
		$objectType = ObjectTypeCache::getInstance()->getObjectType($this->parameters['data']['objectTypeID']);
		if ($this->createdComment->userID && UserActivityEventHandler::getInstance()->getObjectTypeID($objectType->objectType.'.recentActivityEvent')) {
			UserActivityEventHandler::getInstance()->fireEvent($objectType->objectType.'.recentActivityEvent', $this->createdComment->commentID);
		}
		
		// fire notification event
		if (UserNotificationHandler::getInstance()->getObjectTypeID($objectType->objectType.'.notification')) {
			$notificationObject = new CommentUserNotificationObject($this->createdComment);
			$notificationObjectType = UserNotificationHandler::getInstance()->getObjectTypeProcessor($objectType->objectType.'.notification');
			
			if ($notificationObjectType instanceof IMultiRecipientCommentUserNotificationObjectType) {
				$recipientIDs = $notificationObjectType->getRecipientIDs($this->createdComment);
				
				// make sure that the active user gets no notification
				$recipientIDs = array_diff($recipientIDs, [WCF::getUser()->userID]);
				
				if (!empty($recipientIDs)) {
					UserNotificationHandler::getInstance()->fireEvent('comment', $objectType->objectType . '.notification', $notificationObject, $recipientIDs);
				}
			}
			else {
				$userID = $notificationObjectType->getOwnerID($this->createdComment->commentID);
				if ($userID != WCF::getUser()->userID) {
					UserNotificationHandler::getInstance()->fireEvent('comment', $objectType->objectType . '.notification', $notificationObject, [$userID], [
						'objectUserID' => $userID
					]);
				}
			}
		}
		
		if (!$this->createdComment->userID) {
			// save user name is session
			WCF::getSession()->register('username', $this->createdComment->username);
			
			// save last comment time for flood control
			WCF::getSession()->register('lastCommentTime', $this->createdComment->time);
			
			// reset captcha for future requests
			if ($this->captchaObjectType) {
				$this->captchaObjectType->getProcessor()->reset();
			}
		}
		
		return [
			'template' => $this->renderComment($this->createdComment)
		];
	}
	
	/**
	 * Validates parameters to add a response.
	 */
	public function validateAddResponse() {
		CommentHandler::enforceFloodControl();
		
		$this->readInteger('objectID', false, 'data');
		$this->validateMessage();
		
		$this->validateUsername();
		$this->validateCaptcha();
		
		// validate comment id
		$this->validateCommentID();
		
		$objectType = $this->validateObjectType();
		
		// validate object id and permissions
		$this->commentProcessor = $objectType->getProcessor();
		if (!$this->commentProcessor->canAdd($this->parameters['data']['objectID'])) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * Adds a response.
	 * 
	 * @return	array
	 */
	public function addResponse() {
		if (!empty($this->validationErrors)) {
			if (!empty($this->parameters['data']['username'])) {
				WCF::getSession()->register('username', $this->parameters['data']['username']);
			}
			WCF::getTPL()->assign('errorType', $this->validationErrors);
			
			$guestDialog = $this->getGuestDialog();
			return [
				'useCaptcha' => $guestDialog['useCaptcha'],
				'guestDialog' => $guestDialog['template']
			];
		}
		
		// create response
		$this->createdResponse = CommentResponseEditor::create([
			'commentID' => $this->comment->commentID,
			'time' => TIME_NOW,
			'userID' => WCF::getUser()->userID ?: null,
			'username' => WCF::getUser()->userID ? WCF::getUser()->username : $this->parameters['data']['username'],
			'message' => $this->parameters['data']['message']
		]);
		
		// update response data
		$responseIDs = $this->comment->getResponseIDs();
		if (count($responseIDs) < 5) {
			$responseIDs[] = $this->createdResponse->responseID;
		}
		$responses = $this->comment->responses + 1;
		
		// update comment
		$commentEditor = new CommentEditor($this->comment);
		$commentEditor->update([
			'responseIDs' => serialize($responseIDs),
			'responses' => $responses
		]);
		
		// update counter
		$this->commentProcessor->updateCounter($this->parameters['data']['objectID'], 1);
		
		// fire activity event
		$objectType = ObjectTypeCache::getInstance()->getObjectType($this->comment->objectTypeID);
		if ($this->createdResponse->userID && UserActivityEventHandler::getInstance()->getObjectTypeID($objectType->objectType.'.response.recentActivityEvent')) {
			UserActivityEventHandler::getInstance()->fireEvent($objectType->objectType.'.response.recentActivityEvent', $this->createdResponse->responseID);
		}
		
		// fire notification event
		if (UserNotificationHandler::getInstance()->getObjectTypeID($objectType->objectType.'.response.notification')) {
			$notificationObjectType = UserNotificationHandler::getInstance()->getObjectTypeProcessor($objectType->objectType.'.notification');
			$notificationObject = new CommentResponseUserNotificationObject($this->createdResponse);
			
			if ($notificationObjectType instanceof IMultiRecipientCommentUserNotificationObjectType) {
				$recipientIDs = $notificationObjectType->getRecipientIDs($this->comment);
				
				// make sure that the active user gets no notification
				$recipientIDs = array_diff($recipientIDs, [WCF::getUser()->userID]);
				
				if (!empty($recipientIDs)) {
					UserNotificationHandler::getInstance()->fireEvent('commentResponse', $objectType->objectType.'.response.notification', $notificationObject, $recipientIDs, [
						'commentID' => $this->comment->commentID,
						'objectID' => $this->comment->objectID,
						'userID' => $this->comment->userID
					]);
				}
			}
			else {
				$userID = $notificationObjectType->getOwnerID($this->comment->commentID);
				
				if ($this->comment->userID != WCF::getUser()->userID) {
					UserNotificationHandler::getInstance()->fireEvent('commentResponse', $objectType->objectType.'.response.notification', $notificationObject, [$this->comment->userID], [
						'commentID' => $this->comment->commentID,
						'objectID' => $this->comment->objectID,
						'objectUserID' => $userID,
						'userID' => $this->comment->userID
					]);
				}
				
				// notify the container owner
				if (UserNotificationHandler::getInstance()->getObjectTypeID($objectType->objectType.'.notification')) {
					if ($userID != $this->comment->userID && $userID != WCF::getUser()->userID) {
						UserNotificationHandler::getInstance()->fireEvent('commentResponseOwner', $objectType->objectType . '.response.notification', $notificationObject, [$userID], [
							'commentID' => $this->comment->commentID,
							'objectID' => $this->comment->objectID,
							'objectUserID' => $userID,
							'userID' => $this->comment->userID
						]);
					}
				}
			}
		}
		
		if (!$this->createdResponse->userID) {
			// save user name is session
			WCF::getSession()->register('username', $this->createdResponse->username);
			
			// save last comment time for flood control
			WCF::getSession()->register('lastCommentTime', $this->createdResponse->time);
			
			// reset captcha for future requests
			if ($this->captchaObjectType) {
				$this->captchaObjectType->getProcessor()->reset();
			}
		}
		
		return [
			'commentID' => $this->comment->commentID,
			'template' => $this->renderResponse($this->createdResponse),
			'responses' => $responses
		];
	}
	
	/**
	 * Validates parameters to edit a comment or a response.
	 */
	public function validatePrepareEdit() {
		// validate comment id or response id
		try {
			$this->validateCommentID();
		}
		catch (UserInputException $e) {
			try {
				$this->validateResponseID();
			}
			catch (UserInputException $e) {
				throw new UserInputException('objectIDs');
			}
		}
		
		// validate object type id
		$objectType = $this->validateObjectType();
		
		// validate object id and permissions
		$this->commentProcessor = $objectType->getProcessor();
		if ($this->comment !== null) {
			if (!$this->commentProcessor->canEditComment($this->comment)) {
				throw new PermissionDeniedException();
			}
		}
		else {
			if (!$this->commentProcessor->canEditResponse($this->response)) {
				throw new PermissionDeniedException();
			}
		}
	}
	
	/**
	 * Prepares editing of a comment or a response.
	 * 
	 * @return	array
	 */
	public function prepareEdit() {
		if ($this->comment !== null) {
			$message = $this->comment->message;
		}
		else {
			$message = $this->response->message;
		}
		
		$returnValues = [
			'action' => 'prepare',
			'message' => $message
		];
		if ($this->comment !== null) {
			$returnValues['commentID'] = $this->comment->commentID;
		}
		else {
			$returnValues['responseID'] = $this->response->responseID;
		}
		
		return $returnValues;
	}
	
	/**
	 * @inheritDoc
	 */
	public function validateEdit() {
		$this->validatePrepareEdit();
		
		$this->validateMessage();
	}
	
	/**
	 * Edits a comment or response.
	 * 
	 * @return	array
	 */
	public function edit() {
		$returnValues = ['action' => 'saved'];
		
		if ($this->response === null) {
			$editor = new CommentEditor($this->comment);
			$editor->update([
				'message' => $this->parameters['data']['message']
			]);
			$comment = new Comment($this->comment->commentID);
			$returnValues['commentID'] = $this->comment->commentID;
			$returnValues['message'] = $comment->getFormattedMessage();
		}
		else {
			$editor = new CommentResponseEditor($this->response);
			$editor->update([
				'message' => $this->parameters['data']['message']
			]);
			$response = new CommentResponse($this->response->responseID);
			$returnValues['responseID'] = $this->response->responseID;
			$returnValues['message'] = $response->getFormattedMessage();
		}
		
		return $returnValues;
	}
	
	/**
	 * Validates parameters to remove a comment or response.
	 */
	public function validateRemove() {
		// validate comment id or response id
		try {
			$this->validateCommentID();
		}
		catch (UserInputException $e) {
			try {
				$this->validateResponseID();
			}
			catch (UserInputException $e) {
				throw new UserInputException('objectIDs');
			}
		}
		
		// validate object type id
		$objectType = $this->validateObjectType();
		
		// validate object id and permissions
		$this->commentProcessor = $objectType->getProcessor();
		if ($this->comment !== null) {
			if (!$this->commentProcessor->canDeleteComment($this->comment)) {
				throw new PermissionDeniedException();
			}
		}
		else {
			if (!$this->commentProcessor->canDeleteResponse($this->response)) {
				throw new PermissionDeniedException();
			}
		}
	}
	
	/**
	 * Removes a comment or response.
	 * 
	 * @return	integer[]
	 */
	public function remove() {
		if ($this->comment !== null) {
			$objectAction = new CommentAction([$this->comment], 'delete');
			$objectAction->executeAction();
			
			return ['commentID' => $this->comment->commentID];
		}
		else {
			$objectAction = new CommentResponseAction([$this->response], 'delete');
			$objectAction->executeAction();
			
			return ['responseID' => $this->response->responseID];
		}
	}
	
	/**
	 * Validates the 'getGuestDialog' action.
	 */
	public function validateGetGuestDialog() {
		if (WCF::getUser()->userID) {
			throw new PermissionDeniedException();
		}
		
		CommentHandler::enforceFloodControl();
		
		$this->readInteger('objectID', false, 'data');
		$objectType = $this->validateObjectType();
		
		// validate object id and permissions
		$this->commentProcessor = $objectType->getProcessor();
		if (!$this->commentProcessor->canAdd($this->parameters['data']['objectID'])) {
			throw new PermissionDeniedException();
		}
		
		// validate message already at this point to make sure that the
		// message is valid when submitting the dialog to avoid having to
		// go back to the message to fix it
		$this->validateMessage();
	}
	
	/**
	 * Returns the dialog for guests when they try to write a comment letting
	 * them enter a username and solving a captcha.
	 * 
	 * @return	array
	 * @throws	SystemException
	 */
	public function getGuestDialog() {
		$captchaObjectType = null; 
		
		if (CAPTCHA_TYPE) {
			$captchaObjectType = CaptchaHandler::getInstance()->getObjectTypeByName(CAPTCHA_TYPE);
			if ($captchaObjectType === null) {
				throw new SystemException("Unknown captcha object type with name '".CAPTCHA_TYPE."'");
			}
			
			if (!$captchaObjectType->getProcessor()->isAvailable()) {
				$captchaObjectType = null;
			}
		}
		
		return [
			'useCaptcha' => $captchaObjectType !== null,
			'template' => WCF::getTPL()->fetch('commentAddGuestDialog', 'wcf', [
				'ajaxCaptcha' => true,
				'captchaID' => 'commentAdd',
				'captchaObjectType' => $captchaObjectType,
				'username' => WCF::getSession()->getVar('username')
			])
		];
	}
	
	/**
	 * Renders a comment.
	 * 
	 * @param	Comment		$comment
	 * @return	string
	 */
	protected function renderComment(Comment $comment) {
		$comment = new StructuredComment($comment);
		$comment->setIsDeletable($this->commentProcessor->canDeleteComment($comment->getDecoratedObject()));
		$comment->setIsEditable($this->commentProcessor->canEditComment($comment->getDecoratedObject()));
		
		WCF::getTPL()->assign([
			'commentList' => [$comment],
			'commentManager' => $this->commentProcessor
		]);
		return WCF::getTPL()->fetch('commentList');
	}
	
	/**
	 * Renders a response.
	 * 
	 * @param	CommentResponse	$response
	 * @return	string
	 */
	protected function renderResponse(CommentResponse $response) {
		$response = new StructuredCommentResponse($response);
		$response->setIsDeletable($this->commentProcessor->canDeleteResponse($response->getDecoratedObject()));
		$response->setIsEditable($this->commentProcessor->canEditResponse($response->getDecoratedObject()));
		
		// render response
		WCF::getTPL()->assign([
			'responseList' => [$response],
			'commentManager' => $this->commentProcessor
		]);
		return WCF::getTPL()->fetch('commentResponseList');
	}
	
	/**
	 * Validates message parameter.
	 */
	protected function validateMessage() {
		$this->readString('message', false, 'data');
		$this->parameters['data']['message'] = MessageUtil::stripCrap($this->parameters['data']['message']);
		
		if (empty($this->parameters['data']['message'])) {
			throw new UserInputException('message');
		}
		
		CommentHandler::enforceCensorship($this->parameters['data']['message']);
	}
	
	/**
	 * Validates object type id parameter.
	 * 
	 * @return	ObjectType
	 * @throws	UserInputException
	 */
	protected function validateObjectType() {
		$this->readInteger('objectTypeID', false, 'data');
		
		$objectType = ObjectTypeCache::getInstance()->getObjectType($this->parameters['data']['objectTypeID']);
		if ($objectType === null) {
			throw new UserInputException('objectTypeID');
		}
		
		return $objectType;
	}
	
	/**
	 * Validates comment id parameter.
	 */
	protected function validateCommentID() {
		$this->readInteger('commentID', false, 'data');
		
		$this->comment = new Comment($this->parameters['data']['commentID']);
		if ($this->comment === null || !$this->comment->commentID) {
			throw new UserInputException('commentID');
		}
	}
	
	/**
	 * Validates response id parameter.
	 */
	protected function validateResponseID() {
		if (isset($this->parameters['data']['responseID'])) {
			$this->response = new CommentResponse($this->parameters['data']['responseID']);
		}
		if ($this->response === null || !$this->response->responseID) {
			throw new UserInputException('responseID');
		}
	}
	
	/**
	 * Validates the username parameter.
	 */
	protected function validateUsername() {
		if (WCF::getUser()->userID) return;
		
		try {
			$this->readString('username', false, 'data');
			
			if (!UserUtil::isValidUsername($this->parameters['data']['username'])) {
				throw new UserInputException('username', 'notValid');
			}
			if (!UserUtil::isAvailableUsername($this->parameters['data']['username'])) {
				throw new UserInputException('username', 'notUnique');
			}
		}
		catch (UserInputException $e) {
			$this->validationErrors['username'] = $e->getType();
		}
	}
	
	/**
	 * Validates the captcha challenge.
	 */
	protected function validateCaptcha() {
		if (WCF::getUser()->userID) return;
		
		if (CAPTCHA_TYPE) {
			$this->captchaObjectType = CaptchaHandler::getInstance()->getObjectTypeByName(CAPTCHA_TYPE);
			if ($this->captchaObjectType === null) {
				throw new SystemException("Unknown captcha object type with name '".CAPTCHA_TYPE."'");
			}
			
			if (!$this->captchaObjectType->getProcessor()->isAvailable()) {
				$this->captchaObjectType = null;
			}
		}
		
		if ($this->captchaObjectType === null) return;
		
		try {
			$this->captchaObjectType->getProcessor()->readFormParameters();
			$this->captchaObjectType->getProcessor()->validate();
		}
		catch (UserInputException $e) {
			$this->validationErrors = array_merge($this->validationErrors, [$e->getField() => $e->getType()]);
		}
	}
	
	/**
	 * Returns the comment object.
	 * 
	 * @return	Comment
	 */
	public function getComment() {
		return $this->comment;
	}
	
	/**
	 * Returns the comment response object.
	 * 
	 * @return	CommentResponse
	 */
	public function getResponse() {
		return $this->response;
	}
	
	/**
	 * Returns the comment manager.
	 * 
	 * @return	ICommentManager
	 */
	public function getCommentManager() {
		return $this->commentProcessor;
	}
}
