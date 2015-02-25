<?php
namespace wcf\system\like;
use wcf\data\like\object\ILikeObject;
use wcf\data\like\object\LikeObject;
use wcf\data\like\object\LikeObjectEditor;
use wcf\data\like\object\LikeObjectList;
use wcf\data\like\Like;
use wcf\data\like\LikeEditor;
use wcf\data\like\LikeList;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\User;
use wcf\data\user\UserEditor;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\database\DatabaseException;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\user\activity\point\UserActivityPointHandler;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Handles the likes of liked objects.
 * 
 * Usage (retrieve all likes for a list of objects):
 * // get type object
 * $objectType = LikeHandler::getInstance()->getObjectType('com.woltlab.wcf.foo.bar');
 * // load like data
 * LikeHandler::getInstance()->loadLikeObjects($objectType, $objectIDs);
 * // get like data
 * $likeObjects = LikeHandler::getInstance()->getLikeObjects($objectType);
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.like
 * @category	Community Framework
 */
class LikeHandler extends SingletonFactory {
	/**
	 * loaded like objects
	 * @var	array<array>
	 */
	protected $likeObjectCache = array();
	
	/**
	 * cached object types
	 * @var	array<array>
	 */
	protected $cache = null;
	
	/**
	 * Creates a new LikeHandler instance.
	 */
	protected function init() {
		// load cache
		$this->cache = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.like.likeableObject');
	}
	
	/**
	 * Returns an object type from cache.
	 * 
	 * @return	\wcf\data\object\type\ObjectType
	 */
	public function getObjectType($objectName) {
		if (isset($this->cache[$objectName])) {
			return $this->cache[$objectName];
		}
		
		return null;
	}
	
	/**
	 * Gets a like object.
	 * 
	 * @param	\wcf\data\object\type\ObjectType		$objectType
	 * @param	integer					$objectID
	 * @return	\wcf\data\like\object\LikeObject
	 */
	public function getLikeObject(ObjectType $objectType, $objectID) {
		if (isset($this->likeObjectCache[$objectType->objectTypeID][$objectID])) {
			return $this->likeObjectCache[$objectType->objectTypeID][$objectID];
		}
		
		return null;
	}
	
	/**
	 * Gets the like objects of a specific object type.
	 * 
	 * @param	\wcf\data\object\type\ObjectType		$objectType
	 * @return	array<\wcf\data\like\object\LikeObject>
	 */
	public function getLikeObjects(ObjectType $objectType) {
		if (isset($this->likeObjectCache[$objectType->objectTypeID])) {
			return $this->likeObjectCache[$objectType->objectTypeID];
		}
		
		return array();
	}
	
	/**
	 * Loads the like data for a set of objects and returns the number of loaded
	 * like objects
	 * 
	 * @param	\wcf\data\object\type\ObjectType		$objectType
	 * @param	array					$objectIDs
	 * @return	integer
	 */
	public function loadLikeObjects(ObjectType $objectType, array $objectIDs) {
		if (empty($objectIDs)) {
			return 0;
		}
		
		$i = 0;
		
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("like_object.objectTypeID = ?", array($objectType->objectTypeID));
		$conditions->add("like_object.objectID IN (?)", array($objectIDs));
		$parameters = $conditions->getParameters();
		
		if (WCF::getUser()->userID) {
			$sql = "SELECT		like_object.*,
						CASE WHEN like_table.userID IS NOT NULL THEN like_table.likeValue ELSE 0 END AS liked
				FROM		wcf".WCF_N."_like_object like_object
				LEFT JOIN	wcf".WCF_N."_like like_table
				ON		(like_table.objectTypeID = like_object.objectTypeID
						AND like_table.objectID = like_object.objectID
						AND like_table.userID = ?)
				".$conditions;
			
			array_unshift($parameters, WCF::getUser()->userID);
		}
		else {
			$sql = "SELECT		like_object.*, 0 AS liked
				FROM		wcf".WCF_N."_like_object like_object
				".$conditions;
		}
		
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($parameters);
		while ($row = $statement->fetchArray()) {
			$this->likeObjectCache[$objectType->objectTypeID][$row['objectID']] = new LikeObject(null, $row);
			$i++;
		}
		
		return $i;
	}
	
	/**
	 * Saves the like of an object.
	 * 
	 * @param	\wcf\data\like\object\ILikeObject	$likeable
	 * @param	\wcf\data\user\User			$user
	 * @param	integer					$likeValue
	 * @param	integer					$time
	 * @return	array
	 */
	public function like(ILikeObject $likeable, User $user, $likeValue, $time = TIME_NOW) {
		// verify if object is already liked by user
		$like = Like::getLike($likeable->getObjectType()->objectTypeID, $likeable->getObjectID(), $user->userID);
		
		// get like object
		$likeObject = LikeObject::getLikeObject($likeable->getObjectType()->objectTypeID, $likeable->getObjectID());
		
		// if vote is identically just revert the vote
		if ($like->likeID && ($like->likeValue == $likeValue)) {
			return $this->revertLike($like, $likeable, $likeObject, $user);
		}
		
		try {
			WCF::getDB()->beginTransaction();
			
			// like data
			$cumulativeLikes = 0;
			$newValue = $oldValue = null;
			$users = array();
			
			// update existing object
			if ($likeObject->likeObjectID) {
				$likes = $likeObject->likes;
				$dislikes = $likeObject->dislikes;
				$cumulativeLikes = $likeObject->cumulativeLikes;
				
				// previous (dis-)like already exists
				if ($like->likeID) {
					$oldValue = $like->likeValue;
					
					// revert like and replace it with a dislike
					if ($like->likeValue == Like::LIKE) {
						$likes--;
						$dislikes++;
						$cumulativeLikes -= 2;
						$newValue = Like::DISLIKE;
					}
					else {
						// revert dislike and replace it with a like
						$likes++;
						$dislikes--;
						$cumulativeLikes += 2;
						$newValue = Like::LIKE;
					}
				}
				else {
					if ($likeValue == Like::LIKE) {
						$likes++;
						$cumulativeLikes++;
						$newValue = Like::LIKE;
					}
					else {
						$dislikes++;
						$cumulativeLikes--;
						$newValue = Like::DISLIKE;
					}
				}
				
				// build update date
				$updateData = array(
					'likes' => $likes,
					'dislikes' => $dislikes,
					'cumulativeLikes' => $cumulativeLikes
				);
				
				if ($likeValue == 1) {
					$users = unserialize($likeObject->cachedUsers);
					if (count($users) < 3) {
						$users[$user->userID] = array('userID' => $user->userID, 'username' => $user->username);
						$updateData['cachedUsers'] = serialize($users);
					}
				}
				else if ($likeValue == -1) {
					$users = unserialize($likeObject->cachedUsers);
					if (isset($users[$user->userID])) {
						unset($users[$user->userID]);
						$updateData['cachedUsers'] = serialize($users);
					}
				}
				
				// update data
				$likeObjectEditor = new LikeObjectEditor($likeObject);
				$likeObjectEditor->update($updateData);
			}
			else {
				$cumulativeLikes = $likeValue;
				$newValue = $likeValue;
				$users = array();
				if ($likeValue == 1) $users = array($user->userID => array('userID' => $user->userID, 'username' => $user->username));
				
				// create cache
				$likeObject = LikeObjectEditor::create(array(
					'objectTypeID' => $likeable->getObjectType()->objectTypeID,
					'objectID' => $likeable->getObjectID(),
					'objectUserID' => ($likeable->getUserID() ?: null),
					'likes' => ($likeValue == Like::LIKE) ? 1 : 0,
					'dislikes' => ($likeValue == Like::DISLIKE) ? 1 : 0,
					'cumulativeLikes' => $cumulativeLikes,
					'cachedUsers' => serialize($users)
				));
			}
			
			// update owner's like counter
			if ($likeable->getUserID()) {
				if ($like->likeID) {
					$userEditor = new UserEditor(new User($likeable->getUserID()));
					$userEditor->updateCounters(array(
						'likesReceived' => ($like->likeValue == Like::LIKE ? -1 : 1)
					));
				}
				else if ($likeValue == Like::LIKE) {
					$userEditor = new UserEditor(new User($likeable->getUserID()));
					$userEditor->updateCounters(array(
						'likesReceived' => 1
					));
				}
			}
			
			if (!$like->likeID) {
				// save like
				$like = LikeEditor::create(array(
					'objectID' => $likeable->getObjectID(),
					'objectTypeID' => $likeable->getObjectType()->objectTypeID,
					'objectUserID' => ($likeable->getUserID() ?: null),
					'userID' => $user->userID,
					'time' => $time,
					'likeValue' => $likeValue
				));
				
				if ($likeValue == Like::LIKE && $likeable->getUserID()) {
					UserActivityPointHandler::getInstance()->fireEvent('com.woltlab.wcf.like.activityPointEvent.receivedLikes', $like->likeID, $likeable->getUserID());
					$likeable->sendNotification($like);
				}
			}
			else {
				$likeEditor = new LikeEditor($like);
				$likeEditor->update(array(
					'time' => $time,
					'likeValue' => $likeValue
				));
				
				if ($likeable->getUserID()) {
					if ($likeValue == Like::DISLIKE) {
						UserActivityPointHandler::getInstance()->removeEvents('com.woltlab.wcf.like.activityPointEvent.receivedLikes', array($likeable->getUserID() => 1));
					}
					else {
						UserActivityPointHandler::getInstance()->fireEvent('com.woltlab.wcf.like.activityPointEvent.receivedLikes', $like->likeID, $likeable->getUserID());
						$likeable->sendNotification($like);
					}
				}
			}
			
			// update object's like counter
			$likeable->updateLikeCounter($cumulativeLikes);
			
			WCF::getDB()->commitTransaction();
		}
		catch (DatabaseException $e) {
			WCF::getDB()->rollBackTransaction();
		}
		
		return array(
			'data' => $this->loadLikeStatus($likeObject, $user),
			'like' => $like,
			'newValue' => $newValue,
			'oldValue' => $oldValue,
			'users' => $users
		);
	}
	
	/**
	 * Reverts the like of an object.
	 * 
	 * @param	\wcf\data\like\Like			$like
	 * @param	\wcf\data\like\object\ILikeObject	$likeable
	 * @param	\wcf\data\like\object\LikeObject	$likeObject
	 * @param	\wcf\data\user\User			$user
	 * @return	array
	 */
	public function revertLike(Like $like, ILikeObject $likeable, LikeObject $likeObject, User $user) {
		try {
			WCF::getDB()->beginTransaction();
			
			// delete like
			$editor = new LikeEditor($like);
			$editor->delete();
			
			// update like object cache
			$likes = $likeObject->likes;
			$dislikes = $likeObject->dislikes;
			$cumulativeLikes = $likeObject->cumulativeLikes;
			
			if ($like->likeValue == Like::LIKE) {
				$likes--;
				$cumulativeLikes--;
			}
			else {
				$dislikes--;
				$cumulativeLikes++;
			}
			
			// build update data
			$updateData = array(
				'likes' => $likes,
				'dislikes' => $dislikes,
				'cumulativeLikes' => $cumulativeLikes
			);
			
			$users = $likeObject->getUsers();
			$usersArray = array();
			foreach ($users as $user2) {
				$usersArray[$user2->userID] = array('userID' => $user2->userID, 'username' => $user2->username);
			}
			
			if (isset($usersArray[$user->userID])) {
				unset($usersArray[$user->userID]);
				$updateData['cachedUsers'] = serialize($usersArray);
			}
			
			$likeObjectEditor = new LikeObjectEditor($likeObject);
			if (!$updateData['likes'] && !$updateData['dislikes']) {
				// remove object instead
				$likeObjectEditor->delete();
			}
			else {
				// update data
				$likeObjectEditor->update($updateData);
			}
			
			// update owner's like counter and activity points
			if ($likeable->getUserID()) {
				if ($like->likeValue == Like::LIKE) {
					$userEditor = new UserEditor(new User($likeable->getUserID()));
					$userEditor->updateCounters(array(
						'likesReceived' => -1
					));
					
					UserActivityPointHandler::getInstance()->removeEvents('com.woltlab.wcf.like.activityPointEvent.receivedLikes', array($likeable->getUserID() => 1));
				}
			}
			
			// update object's like counter
			$likeable->updateLikeCounter($cumulativeLikes);
			
			WCF::getDB()->commitTransaction();
		}
		catch (DatabaseException $e) {
			WCF::getDB()->rollBackTransaction();
		}
		
		return array(
			'data' => $this->loadLikeStatus($likeObject, $user),
			'newValue' => null,
			'oldValue' => $like->likeValue,
			'users' => $usersArray
		);
	}
	
	/**
	 * Removes all likes for given objects.
	 * 
	 * @param	string			$objectType
	 * @param	array<integer>		$objectIDs
	 * @param	array<string>		$notificationObjectTypes
	 */
	public function removeLikes($objectType, array $objectIDs, array $notificationObjectTypes = array()) {
		$objectTypeObj = $this->getObjectType($objectType);
		
		// get like objects
		$likeObjectList = new LikeObjectList();
		$likeObjectList->getConditionBuilder()->add('like_object.objectTypeID = ?', array($objectTypeObj->objectTypeID));
		$likeObjectList->getConditionBuilder()->add('like_object.objectID IN (?)', array($objectIDs));
		$likeObjectList->readObjects();
		$likeObjects = $likeObjectList->getObjects();
		$likeObjectIDs = $likeObjectList->getObjectIDs();
		
		// reduce count of received users
		$users = array();
		foreach ($likeObjects as $likeObject) {
			if ($likeObject->likes) {
				if (!isset($users[$likeObject->objectUserID])) $users[$likeObject->objectUserID] = 0;
				$users[$likeObject->objectUserID] += $likeObject->likes;
			}
		}
		foreach ($users as $userID => $receivedLikes) {
			$userEditor = new UserEditor(new User(null, array('userID' => $userID)));
			$userEditor->updateCounters(array(
				'likesReceived' => $receivedLikes * -1
			));
		}
		
		// get like ids
		$likeList = new LikeList();
		$likeList->getConditionBuilder()->add('like_table.objectTypeID = ?', array($objectTypeObj->objectTypeID));
		$likeList->getConditionBuilder()->add('like_table.objectID IN (?)', array($objectIDs));
		$likeList->readObjects();
		
		if (count($likeList)) {
			$likeData = array();
			foreach ($likeList as $like) {
				$likeData[$like->likeID] = $like->userID;
			}
			
			// delete like notifications
			if (!empty($notificationObjectTypes)) {
				foreach ($notificationObjectTypes as $notificationObjectType) {
					UserNotificationHandler::getInstance()->removeNotifications($notificationObjectType, $likeList->getObjectIDs());
				}
			}
			else if (UserNotificationHandler::getInstance()->getObjectTypeID($objectType.'.notification')) {
				UserNotificationHandler::getInstance()->removeNotifications($objectType.'.notification', $likeList->getObjectIDs());
			}
			
			// revoke activity points
			UserActivityPointHandler::getInstance()->removeEvents('com.woltlab.wcf.like.activityPointEvent.receivedLikes', $likeData);
			
			// delete likes
			LikeEditor::deleteAll(array_keys($likeData));
		}
		
		// delete like objects
		if (!empty($likeObjectIDs)) {
			LikeObjectEditor::deleteAll($likeObjectIDs);
		}
		
		// delete activity events
		if (UserActivityEventHandler::getInstance()->getObjectTypeID($objectTypeObj->objectType.'.recentActivityEvent')) {
			UserActivityEventHandler::getInstance()->removeEvents($objectTypeObj->objectType.'.recentActivityEvent', $objectIDs);
		}
	}
	
	/**
	 * Returns current like object status.
	 * 
	 * @param	\wcf\data\like\object\LikeObject		$likeObject
	 * @param	\wcf\data\user\User			$user
	 * @return	array
	 */
	protected function loadLikeStatus(LikeObject $likeObject, User $user) {
		$sql = "SELECT		like_object.likes, like_object.dislikes, like_object.cumulativeLikes,
					CASE WHEN like_table.likeValue IS NOT NULL THEN like_table.likeValue ELSE 0 END AS liked
			FROM		wcf".WCF_N."_like_object like_object
			LEFT JOIN	wcf".WCF_N."_like like_table
			ON		(like_table.objectTypeID = ".$likeObject->objectTypeID."
					AND like_table.objectID = like_object.objectID
					AND like_table.userID = ?)
			WHERE		like_object.likeObjectID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$user->userID,
			$likeObject->likeObjectID
		));
		$row = $statement->fetchArray();
		
		return $row;
	}
}
