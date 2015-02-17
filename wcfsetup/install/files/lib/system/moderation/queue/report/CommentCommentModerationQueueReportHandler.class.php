<?php
namespace wcf\system\moderation\queue\report;
use wcf\data\comment\Comment;
use wcf\data\comment\CommentAction;
use wcf\data\comment\CommentList;
use wcf\data\comment\ViewableComment;
use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\moderation\queue\ViewableModerationQueue;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\moderation\queue\AbstractModerationQueueHandler;
use wcf\system\moderation\queue\ModerationQueueManager;
use wcf\system\WCF;

/**
 * An implementation of IModerationQueueReportHandler for comments.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.moderation.queue
 * @category	Community Framework
 */
class CommentCommentModerationQueueReportHandler extends AbstractModerationQueueHandler implements IModerationQueueReportHandler {
	/**
	 * @see	\wcf\system\moderation\queue\AbstractModerationQueueHandler::$className
	 */
	protected $className = 'wcf\data\comment\Comment';
	
	/**
	 * @see	\wcf\system\moderation\queue\AbstractModerationQueueHandler::$definitionName
	 */
	protected $definitionName = 'com.woltlab.wcf.moderation.report';
	
	/**
	 * @see	\wcf\system\moderation\queue\AbstractModerationQueueHandler::$objectType
	 */
	protected $objectType = 'com.woltlab.wcf.comment.comment';
	
	/**
	 * list of comments
	 * @var	array<\wcf\data\comment\Comment>
	 */
	protected static $comments = array();
	
	/**
	 * list of comment managers
	 * @var	array<\wcf\system\comment\manager\ICommentManager>
	 */
	protected static $commentManagers = array();
	
	/**
	 * @see	\wcf\system\moderation\queue\IModerationQueueHandler::assignQueues()
	 */
	public function assignQueues(array $queues) {
		$assignments = array();
		
		// read comments
		$commentIDs = array();
		foreach ($queues as $queue) {
			$commentIDs[] = $queue->objectID;
		}
		
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("commentID IN (?)", array($commentIDs));
		
		$sql = "SELECT	commentID, objectTypeID, objectID
			FROM	wcf".WCF_N."_comment
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		$comments = array();
		while ($row = $statement->fetchArray()) {
			$comments[$row['commentID']] = new Comment(null, $row);
		}
		
		$orphanedQueueIDs = array();
		foreach ($queues as $queue) {
			$assignUser = false;
			
			if (!isset($comments[$queue->objectID])) {
				$orphanedQueueIDs[] = $queue->queueID;
				continue;
			}
			
			$comment = $comments[$queue->objectID];
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
		
		$comment = $this->getComment($objectID);
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
			'message' => ViewableComment::getComment($queue->objectID)
		));
		
		return WCF::getTPL()->fetch('moderationComment');
	}
	
	/**
	 * @see	\wcf\system\moderation\queue\report\IModerationQueueReportHandler::getReportedObject()
	 */
	public function getReportedObject($objectID) {
		return $this->getComment($objectID);
	}
	
	/**
	 * @see	\wcf\system\moderation\queue\IModerationQueueHandler::isValid()
	 */
	public function isValid($objectID) {
		if ($this->getComment($objectID) === null) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Returns a comment object by comment id or null if comment id is invalid.
	 * 
	 * @param	integer		$objectID
	 * @return	\wcf\data\comment\Comment
	 */
	protected function getComment($objectID) {
		if (!array_key_exists($objectID, self::$comments)) {
			self::$comments[$objectID] = new Comment($objectID);
			if (!self::$comments[$objectID]->commentID) {
				self::$comments[$objectID] = null;
			}
		}
		
		return self::$comments[$objectID];
	}
	
	/**
	 * Returns a comment manager for given comment.
	 * 
	 * @param	\wcf\data\comment\Comment	$comment
	 * @return	\wcf\system\comment\manager\ICommentManager
	 */
	protected function getCommentManager(Comment $comment) {
		if (!isset(self::$commentManagers[$comment->objectTypeID])) {
			self::$commentManagers[$comment->objectTypeID] = ObjectTypeCache::getInstance()->getObjectType($comment->objectTypeID)->getProcessor();
		}
		
		return self::$commentManagers[$comment->objectTypeID];
	}
	
	/**
	 * @see	\wcf\system\moderation\queue\IModerationQueueHandler::populate()
	 */
	public function populate(array $queues) {
		$objectIDs = array();
		foreach ($queues as $object) {
			$objectIDs[] = $object->objectID;
		}
		
		// fetch comments
		$commentList = new CommentList();
		$commentList->getConditionBuilder()->add("comment.commentID IN (?)", array($objectIDs));
		$commentList->readObjects();
		$comments = $commentList->getObjects();
		
		foreach ($queues as $object) {
			if (isset($comments[$object->objectID])) {
				$object->setAffectedObject($comments[$object->objectID]);
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
			$commentAction = new CommentAction(array($this->getComment($queue->objectID)), 'delete');
			$commentAction->executeAction();
		}
	}
}
