<?php
namespace wcf\system\comment;
use wcf\data\comment\response\CommentResponseList;
use wcf\data\comment\CommentEditor;
use wcf\data\comment\CommentList;
use wcf\data\comment\StructuredCommentList;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\comment\manager\ICommentManager;
use wcf\system\exception\NamedUserException;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\like\LikeHandler;
use wcf\system\message\censorship\Censorship;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Provides methods for comment object handling.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Comment
 */
class CommentHandler extends SingletonFactory {
	/**
	 * cached object types
	 * @var	mixed[][]
	 */
	protected $cache = null;
	
	/**
	 * @inheritDoc
	 */
	protected function init() {
		$this->cache = [
			'objectTypes' => [],
			'objectTypeIDs' => []
		];
		
		$cache = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.comment.commentableContent');
		foreach ($cache as $objectType) {
			$this->cache['objectTypes'][$objectType->objectTypeID] = $objectType;
			$this->cache['objectTypeIDs'][$objectType->objectType] = $objectType->objectTypeID;
		}
	}
	
	/**
	 * Returns the object type id for a given object type.
	 * 
	 * @param	string		$objectType
	 * @return	integer
	 */
	public function getObjectTypeID($objectType) {
		if (isset($this->cache['objectTypeIDs'][$objectType])) {
			return $this->cache['objectTypeIDs'][$objectType];
		}
		
		return null;
	}
	
	/**
	 * Returns the object type for a given object type id.
	 * 
	 * @param	integer		$objectTypeID
	 * @return	ObjectType
	 */
	public function getObjectType($objectTypeID) {
		if (isset($this->cache['objectTypes'][$objectTypeID])) {
			return $this->cache['objectTypes'][$objectTypeID];
		}
		
		return null;
	}
	
	/**
	 * Returns comment manager object for given object type.
	 * 
	 * @param	string		$objectType
	 * @return	ICommentManager
	 * @throws	SystemException
	 */
	public function getCommentManager($objectType) {
		$objectTypeID = $this->getObjectTypeID($objectType);
		if ($objectTypeID === null) {
			throw new SystemException("Unable to find object type for '".$objectType."'");
		}
		
		return $this->getObjectType($objectTypeID)->getProcessor();
	}
	
	/**
	 * Returns a comment list for a given object type and object id.
	 * 
	 * @param	ICommentManager		$commentManager
	 * @param	integer			$objectTypeID
	 * @param	integer			$objectID
	 * @param	boolean			$readObjects
	 * @return	StructuredCommentList
	 */
	public function getCommentList(ICommentManager $commentManager, $objectTypeID, $objectID, $readObjects = true) {
		$commentList = new StructuredCommentList($commentManager, $objectTypeID, $objectID);
		if ($readObjects) {
			$commentList->readObjects();
		}
		
		return $commentList;
	}
	
	/**
	 * Removes all comments for given objects.
	 * 
	 * @param	string		$objectType
	 * @param	integer[]	$objectIDs
	 */
	public function deleteObjects($objectType, array $objectIDs) {
		$objectTypeID = $this->getObjectTypeID($objectType);
		$objectTypeObj = $this->getObjectType($objectTypeID);
		
		// get comment ids
		$commentList = new CommentList();
		$commentList->getConditionBuilder()->add('comment.objectTypeID = ?', [$objectTypeID]);
		$commentList->getConditionBuilder()->add('comment.objectID IN (?)', [$objectIDs]);
		$commentList->readObjectIDs();
		$commentIDs = $commentList->getObjectIDs();
		
		// no comments -> skip
		if (empty($commentIDs)) return;
		
		// get response ids
		$responseList = new CommentResponseList();
		$responseList->getConditionBuilder()->add('comment_response.commentID IN (?)', [$commentIDs]);
		$responseList->readObjectIDs();
		$responseIDs = $responseList->getObjectIDs();
		
		// delete likes
		$notificationObjectTypes = [];
		if (UserNotificationHandler::getInstance()->getObjectTypeID($objectTypeObj->objectType.'.like.notification')) {
			$notificationObjectTypes[] = $objectTypeObj->objectType.'.like.notification';
		}
		
		LikeHandler::getInstance()->removeLikes('com.woltlab.wcf.comment', $commentIDs, $notificationObjectTypes);
		
		// delete activity events
		if (UserActivityEventHandler::getInstance()->getObjectTypeID($objectTypeObj->objectType.'.recentActivityEvent')) {
			UserActivityEventHandler::getInstance()->removeEvents($objectTypeObj->objectType.'.recentActivityEvent', $commentIDs);
		}
		// delete notifications
		if (UserNotificationHandler::getInstance()->getObjectTypeID($objectTypeObj->objectType.'.notification')) {
			UserNotificationHandler::getInstance()->removeNotifications($objectTypeObj->objectType.'.notification', $commentIDs);
		}
		
		if (!empty($responseIDs)) {
			// delete likes (for responses)
			$notificationObjectTypes = [];
			if (UserNotificationHandler::getInstance()->getObjectTypeID($objectTypeObj->objectType.'.response.like.notification')) {
				$notificationObjectTypes[] = $objectTypeObj->objectType.'.response.like.notification';
			}
			
			LikeHandler::getInstance()->removeLikes('com.woltlab.wcf.comment.response', $responseIDs, $notificationObjectTypes);
			
			// delete activity events (for responses)
			if (UserActivityEventHandler::getInstance()->getObjectTypeID($objectTypeObj->objectType.'.response.recentActivityEvent')) {
				UserActivityEventHandler::getInstance()->removeEvents($objectTypeObj->objectType.'.response.recentActivityEvent', $responseIDs);
			}
			// delete notifications (for responses)
			if (UserNotificationHandler::getInstance()->getObjectTypeID($objectTypeObj->objectType.'.response.notification')) {
				UserNotificationHandler::getInstance()->removeNotifications($objectTypeObj->objectType.'.response.notification', $responseIDs);
			}
		}
		
		// delete comments / responses
		CommentEditor::deleteAll($commentIDs);
	}
	
	/**
	 * Enforces the flood control.
	 */
	public static function enforceFloodControl() {
		if (!WCF::getSession()->getPermission('user.comment.floodControlTime')) {
			return;
		}
		
		// flood control for guests is session based
		if (!WCF::getUser()->userID) {
			$lastCommentTime = WCF::getSession()->getVar('lastCommentTime');
			
			if ($lastCommentTime && $lastCommentTime + WCF::getSession()->getPermission('user.comment.floodControlTime') > TIME_NOW) {
				throw new NamedUserException(WCF::getLanguage()->getDynamicVariable('wcf.comment.error.floodControl', [
					'lastCommentTime' => $lastCommentTime
				]));
			}
			
			return;
		}
		
		// check for comments
		$sql = "SELECT		time
			FROM		wcf".WCF_N."_comment
			WHERE		userID = ?
					AND time > ?
			ORDER BY	time DESC";
		$statement = WCF::getDB()->prepareStatement($sql, 1);
		$statement->execute([
			WCF::getUser()->userID,
			(TIME_NOW - WCF::getSession()->getPermission('user.comment.floodControlTime'))
		]);
		if (($row = $statement->fetchArray()) !== false) {
			throw new NamedUserException(WCF::getLanguage()->getDynamicVariable('wcf.comment.error.floodControl', ['lastCommentTime' => $row['time']]));
		}
		else {
			// check for comment response
			$sql = "SELECT		time
				FROM		wcf".WCF_N."_comment_response
				WHERE		userID = ?
						AND time > ?
				ORDER BY	time DESC";
			$statement = WCF::getDB()->prepareStatement($sql, 1);
			$statement->execute([
				WCF::getUser()->userID,
				(TIME_NOW - WCF::getSession()->getPermission('user.comment.floodControlTime'))
			]);
			if (($row = $statement->fetchArray()) !== false) {
				throw new NamedUserException(WCF::getLanguage()->getDynamicVariable('wcf.comment.error.floodControl', ['lastCommentTime' => $row['time']]));
			}
		}
	}
	
	/**
	 * Enforces the censorship.
	 * 
	 * @param	string		$text
	 * @throws	UserInputException
	 */
	public static function enforceCensorship($text) {
		// search for censored words
		if (ENABLE_CENSORSHIP) {
			$result = Censorship::getInstance()->test($text);
			if ($result) {
				throw new UserInputException('text', WCF::getLanguage()->getDynamicVariable('wcf.message.error.censoredWordsFound', ['censoredWords' => $result]));
			}
		}
	}
}
