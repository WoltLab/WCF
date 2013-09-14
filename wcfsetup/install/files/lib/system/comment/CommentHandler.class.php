<?php
namespace wcf\system\comment;
use wcf\data\comment\response\CommentResponseList;
use wcf\data\comment\CommentEditor;
use wcf\data\comment\CommentList;
use wcf\data\comment\StructuredCommentList;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\comment\manager\ICommentManager;
use wcf\system\exception\NamedUserException;
use wcf\system\exception\SystemException;
use wcf\system\like\LikeHandler;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Provides methods for comment object handling.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.comment
 * @category	Community Framework
 */
class CommentHandler extends SingletonFactory {
	/**
	 * cached object types
	 * @var	array<array>
	 */
	protected $cache = null;
	
	/**
	 * @see	wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		$this->cache = array(
			'objectTypes' => array(),
			'objectTypeIDs' => array()
		);
		
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
	 * @return	wcf\data\object\type\ObjectType
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
	 * @return	wcf\system\comment\manager\ICommentManager
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
	 * @param	wcf\data\comment\manager\ICommentManager	$commentManager
	 * @param	integer						$objectTypeID
	 * @param	integer						$objectID
	 * @param	boolean						$readObjects
	 * @return	wcf\data\comment\StructuredCommentList
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
	 * @param	array<integer>	$objectIDs
	 */
	public function deleteObjects($objectType, array $objectIDs) {
		$objectTypeID = $this->getObjectTypeID($objectType);
		$objectTypeObj = $this->getObjectType($objectTypeID);
		
		// get comment ids
		$commentList = new CommentList();
		$commentList->getConditionBuilder()->add('comment.objectTypeID = ?', array($objectTypeID));
		$commentList->getConditionBuilder()->add('comment.objectID IN (?)', array($objectIDs));
		$commentList->readObjectIDs();
		$commentIDs = $commentList->getObjectIDs();
		
		// no comments -> skip
		if (empty($commentIDs)) return;
		
		// get response ids
		$responseList = new CommentResponseList();
		$responseList->getConditionBuilder()->add('comment_response.commentID IN (?)', array($commentIDs));
		$responseList->readObjectIDs();
		$responseIDs = $responseList->getObjectIDs();
		
		// delete likes
		LikeHandler::getInstance()->removeLikes('com.woltlab.wcf.comment', $commentIDs);
		
		// delete activity events
		if (UserActivityEventHandler::getInstance()->getObjectTypeID($objectTypeObj->objectType.'.recentActivityEvent')) {
			UserActivityEventHandler::getInstance()->removeEvents($objectTypeObj->objectType.'.recentActivityEvent', $commentIDs);
		}
		// delete notifications
		if (UserNotificationHandler::getInstance()->getObjectTypeID($objectTypeObj->objectType.'.notification')) {
			UserNotificationHandler::getInstance()->deleteNotifications('comment', $objectTypeObj->objectType.'.notification', array(), $commentIDs);
		}
		
		if (!empty($responseIDs)) {
			// delete activity events (for responses)
			if (UserActivityEventHandler::getInstance()->getObjectTypeID($objectTypeObj->objectType.'.response.recentActivityEvent')) {
				UserActivityEventHandler::getInstance()->removeEvents($objectTypeObj->objectType.'.response.recentActivityEvent', $responseIDs);
			}
			// delete notifications (for responses)
			if (UserNotificationHandler::getInstance()->getObjectTypeID($objectTypeObj->objectType.'.response.notification')) {
				UserNotificationHandler::getInstance()->deleteNotifications('commentResponse', $objectTypeObj->objectType.'.response.notification', array(), $responseIDs);
				UserNotificationHandler::getInstance()->deleteNotifications('commentResponseOwner', $objectTypeObj->objectType.'.response.notification', array(), $responseIDs);
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
		
		// check for comments
		$sql = "SELECT		time
			FROM		wcf".WCF_N."_comment
			WHERE		userID = ?
					AND time > ?
			ORDER BY	time DESC";
		$statement = WCF::getDB()->prepareStatement($sql, 1);
		$statement->execute(array(
			WCF::getUser()->userID,
			(TIME_NOW - WCF::getSession()->getPermission('user.comment.floodControlTime'))
		));
		if (($row = $statement->fetchArray()) !== false) {
			throw new NamedUserException(WCF::getLanguage()->getDynamicVariable('wcf.comment.error.floodControl', array('lastCommentTime' => $row['time'])));
		}
		else {
			// check for comment response
			$sql = "SELECT		time
				FROM		wcf".WCF_N."_comment_response
				WHERE		userID = ?
						AND time > ?
				ORDER BY	time DESC";
			$statement = WCF::getDB()->prepareStatement($sql, 1);
			$statement->execute(array(
				WCF::getUser()->userID,
				(TIME_NOW - WCF::getSession()->getPermission('user.comment.floodControlTime'))
			));
			if (($row = $statement->fetchArray()) !== false) {
				throw new NamedUserException(WCF::getLanguage()->getDynamicVariable('wcf.comment.error.floodControl', array('lastCommentTime' => $row['time'])));
			}
		}
	}
}
