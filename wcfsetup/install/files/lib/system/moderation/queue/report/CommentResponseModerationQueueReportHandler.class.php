<?php
namespace wcf\system\moderation\queue\report;
use wcf\data\comment\response\CommentResponse;
use wcf\data\comment\response\CommentResponseAction;
use wcf\data\comment\response\CommentResponseList;
use wcf\data\comment\response\ViewableCommentResponse;
use wcf\data\comment\Comment;
use wcf\data\comment\CommentList;
use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\moderation\queue\ViewableModerationQueue;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\moderation\queue\ModerationQueueManager;
use wcf\system\WCF;

/**
 * An implementation of IModerationQueueReportHandler for comment responses.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.moderation.queue
 * @category	Community Framework
 */
class CommentResponseModerationQueueReportHandler extends CommentCommentModerationQueueReportHandler {
	/**
	 * @see	\wcf\system\moderation\queue\AbstractModerationQueueHandler::$className
	 */
	protected $className = 'wcf\data\comment\response\CommentResponse';
	
	/**
	 * @see	\wcf\system\moderation\queue\AbstractModerationQueueHandler::$objectType
	 */
	protected $objectType = 'com.woltlab.wcf.comment.response';
	
	/**
	 * list of comment responses
	 * @var	array<\wcf\data\comment\response\CommentResponse>
	 */
	protected static $responses = array();
	
	/**
	 * @see	\wcf\system\moderation\queue\IModerationQueueHandler::assignQueues()
	 */
	public function assignQueues(array $queues) {
		$assignments = array();
		
		// read comments and responses
		$responseIDs = array();
		foreach ($queues as $queue) {
			$responseIDs[] = $queue->objectID;
		}
		
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("comment_response.responseID IN (?)", array($responseIDs));
		
		$sql = "SELECT		comment_response.responseID, comment.commentID, comment.objectTypeID, comment.objectID
			FROM		wcf".WCF_N."_comment_response comment_response
			LEFT JOIN	wcf".WCF_N."_comment comment
			ON		(comment.commentID = comment_response.commentID)
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		$comments = $responses = array();
		while ($row = $statement->fetchArray()) {
			$comments[$row['commentID']] = new Comment(null, $row);
			$responses[$row['responseID']] = new CommentResponse(null, $row);
		}
		
		$orphanedQueueIDs = array();
		foreach ($queues as $queue) {
			$assignUser = false;
			
			if (!isset($responses[$queue->objectID]) || !isset($comments[$responses[$queue->objectID]->commentID])) {
				$orphanedQueueIDs[] = $queue->queueID;
				continue;
			}
			
			$comment = $comments[$responses[$queue->objectID]->commentID];
			if ($this->getCommentManager($comment)->canModerate($comment->objectTypeID, $comment->objectID)) {
				$assignUser = true;
			}
			
			$assignments[$queue->queueID] = $assignUser;
		}
		
		ModerationQueueManager::getInstance()->removeOrphans($orphanedQueueIDs);
		ModerationQueueManager::getInstance()->setAssignment($assignments);
	}
	
	/**
	 * @see	\wcf\system\moderation\queue\report\IModerationQueueReportHandler::canReport()
	 */
	public function canReport($objectID) {
		if (!$this->isValid($objectID)) {
			return false;
		}
		
		$response = $this->getResponse($objectID);
		$comment = $this->getComment($response->commentID);
		if (!$this->getCommentManager($comment)->isAccessible($comment->objectID)) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * @see	\wcf\system\moderation\queue\IModerationQueueHandler::getContainerID()
	 */
	public function getContainerID($objectID) {
		return 0;
	}
	
	/**
	 * @see	\wcf\system\moderation\queue\report\IModerationQueueReportHandler::getReportedContent()
	 */
	public function getReportedContent(ViewableModerationQueue $queue) {
		WCF::getTPL()->assign(array(
			'message' => ViewableCommentResponse::getResponse($queue->objectID)
		));
		
		return WCF::getTPL()->fetch('moderationComment');
	}
	
	/**
	 * @see	\wcf\system\moderation\queue\report\IModerationQueueReportHandler::getReportedObject()
	 */
	public function getReportedObject($objectID) {
		return $this->getResponse($objectID);
	}
	
	/**
	 * @see	\wcf\system\moderation\queue\IModerationQueueHandler::isValid()
	 */
	public function isValid($objectID) {
		if ($this->getResponse($objectID) === null) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Returns a comment response object by response id or null if response id is invalid.
	 * 
	 * @param	integer		$objectID
	 * @return	\wcf\data\comment\response\CommentResponse
	 */
	protected function getResponse($objectID) {
		if (!array_key_exists($objectID, self::$responses)) {
			self::$responses[$objectID] = new CommentResponse($objectID);
			if (!self::$responses[$objectID]->responseID) {
				self::$responses[$objectID] = null;
			}
		}
		
		return self::$responses[$objectID];
	}
	
	/**
	 * @see	\wcf\system\moderation\queue\IModerationQueueHandler::populate()
	 */
	public function populate(array $queues) {
		$objectIDs = array();
		foreach ($queues as $object) {
			$objectIDs[] = $object->objectID;
		}
		
		// fetch responses
		$responseList = new CommentResponseList();
		$responseList->getConditionBuilder()->add("comment_response.responseID IN (?)", array($objectIDs));
		$responseList->readObjects();
		$responses = $responseList->getObjects();
		
		// fetch comments
		$commentIDs = array();
		foreach ($responses as $response) {
			$commentIDs[] = $response->commentID;
		}
		
		if (!empty($commentIDs)) {
			$commentList = new CommentList();
			$commentList->getConditionBuilder()->add("comment.commentID IN (?)", array($commentIDs));
			$commentList->readObjects();
			$comments = $commentList->getObjects();
		}
		
		foreach ($queues as $object) {
			if (isset($responses[$object->objectID])) {
				$response = $responses[$object->objectID];
				$response->setComment($comments[$response->commentID]);
				
				$object->setAffectedObject($response);
			}
			else {
				$object->setIsOrphaned();
			}
		}
	}
	
	/**
	 * @see	\wcf\system\moderation\queue\IModerationQueueHandler::removeContent()
	 */
	public function removeContent(ModerationQueue $queue, $message) {
		if ($this->isValid($queue->objectID)) {
			$responseAction = new CommentResponseAction(array($this->getResponse($queue->objectID)), 'delete');
			$responseAction->executeAction();
		}
	}
}
