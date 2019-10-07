<?php
namespace wcf\system\reaction;
use wcf\data\like\ILikeObjectTypeProvider;
use wcf\data\like\LikeList;
use wcf\data\like\object\ILikeObject;
use wcf\data\like\object\LikeObject;
use wcf\data\like\object\LikeObjectEditor;
use wcf\data\like\Like;
use wcf\data\like\LikeEditor;
use wcf\data\like\object\LikeObjectList;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\reaction\object\IReactionObject;
use wcf\data\reaction\type\ReactionType;
use wcf\data\reaction\type\ReactionTypeCache;
use wcf\data\user\User;
use wcf\data\user\UserEditor;
use wcf\system\cache\runtime\UserRuntimeCache;
use wcf\system\database\exception\DatabaseQueryException;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\event\EventHandler;
use wcf\system\exception\ImplementationException;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\user\activity\point\UserActivityPointHandler;
use wcf\system\SingletonFactory;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\WCF;
use wcf\util\JSON;
use wcf\util\StringUtil;

/**
 * Handles the reactions of objects.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Reaction
 * @since	5.2
 */
class ReactionHandler extends SingletonFactory {
	/**
	 * loaded like objects
	 * @var	LikeObject[][]
	 */
	protected $likeObjectCache = [];
	
	/**
	 * cached object types
	 * @var	ObjectType[]
	 */
	protected $cache = null;
	
	/**
	 * Cache for likeable objects sorted by objectType.
	 * @var ILikeObject[][] 
	 */
	private $likeableObjectsCache = [];
	
	/**
	 * Creates a new ReactionHandler instance.
	 */
	protected function init() {
		$this->cache = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.like.likeableObject');
	}
	
	/**
	 * Returns the JSON encoded JavaScript variable for the template. 
	 * 
	 * @return string
	 */
	public function getReactionsJSVariable() {
		$reactions = ReactionTypeCache::getInstance()->getReactionTypes();
		
		$returnValues = []; 
		
		foreach ($reactions as $reaction) {
			$returnValues[$reaction->reactionTypeID] = [
				'title' => $reaction->getTitle(), 
				'renderedIcon' => $reaction->renderIcon(), 
				'iconPath' => $reaction->getIconPath(), 
				'showOrder' => $reaction->showOrder, 
				'reactionTypeID' => $reaction->reactionTypeID, 
				'isAssignable' => $reaction->isAssignable,
			];
		}
		
		return JSON::encode($returnValues);
	}
	
	/**
	 * Returns all enabled reaction types. 
	 * 
	 * @return      ReactionType[]
	 */
	public function getReactionTypes() {
		return ReactionTypeCache::getInstance()->getReactionTypes();
	}
	
	/**
	 * Returns a reaction type by id. 
	 * 
	 * @param       integer                 $reactionID
	 * @return      ReactionType|null
	 */
	public function getReactionTypeByID($reactionID) {
		return ReactionTypeCache::getInstance()->getReactionTypeByID($reactionID);
	}
	
	/**
	 * Builds the data attributes for the object container. 
	 * 
	 * @param       string          $objectTypeName
	 * @param       integer         $objectID
	 * @return      string
	 */
	public function getDataAttributes($objectTypeName, $objectID) {
		$object = $this->getLikeableObject($objectTypeName, $objectID);
		
		$dataAttributes = [
			'object-id' => $object->getObjectID(),
			'object-type' => $objectTypeName,
			'user-id' => $object->getUserID()
		];
		
		EventHandler::getInstance()->fireAction($this, 'getDataAttributes', $dataAttributes);
		
		$returnDataAttributes = '';
		
		foreach ($dataAttributes as $key => $value) {
			if (!preg_match('/^[a-z0-9-]+$/', $key)) {
				throw new \RuntimeException("Invalid key '". $key ."' for data attribute.");
			}
			
			if (!empty($returnDataAttributes)) {
				$returnDataAttributes .= ' ';
			}
			
			$returnDataAttributes .= 'data-'. $key .'="'. StringUtil::encodeHTML($value) .'"';
		}
		
		return $returnDataAttributes;
	}
	
	/**
	 * Cache likeable objects. 
	 * 
	 * @param       string          $objectTypeName
	 * @param       integer[]       $objectIDs
	 */
	public function cacheLikeableObjects($objectTypeName, array $objectIDs) {
		$objectType = $this->getObjectType($objectTypeName);
		if ($objectType === null) {
			throw new \InvalidArgumentException("ObjectName '{$objectTypeName}' is unknown for definition 'com.woltlab.wcf.like.likeableObject'.");
		}
		
		/** @var ILikeObjectTypeProvider $objectTypeProcessor */
		$objectTypeProcessor = $objectType->getProcessor();
		
		$objects = $objectTypeProcessor->getObjectsByIDs($objectIDs);
		
		if (!isset($this->likeableObjectsCache[$objectTypeName])) {
			$this->likeableObjectsCache[$objectTypeName] = [];
		}
		
		foreach ($objects as $object) {
			$this->likeableObjectsCache[$objectTypeName][$object->getObjectID()] = $object; 
		}
	}
	
	/**
	 * Get an likeable object from the internal cache. 
	 * 
	 * @param       string          $objectTypeName
	 * @param       integer         $objectID
	 * @return      ILikeObject
	 */
	public function getLikeableObject($objectTypeName, $objectID) {
		if (!isset($this->likeableObjectsCache[$objectTypeName][$objectID])) {
			$this->cacheLikeableObjects($objectTypeName, [$objectID]);
		}
		
		if (!isset($this->likeableObjectsCache[$objectTypeName][$objectID])) {
			throw new \InvalidArgumentException("Object with the object id '{$objectID}' for object type '{$objectTypeName}' is unknown.");
		}
		
		if (!($this->likeableObjectsCache[$objectTypeName][$objectID] instanceof ILikeObject)) {
			throw new ImplementationException(get_class($this->likeableObjectsCache[$objectTypeName][$objectID]), ILikeObject::class);
		}
		
		return $this->likeableObjectsCache[$objectTypeName][$objectID];
	}
	
	/**
	 * Returns an object type from cache.
	 *
	 * @param	string		        $objectName
	 * @return	ObjectType|null
	 */
	public function getObjectType($objectName) {
		if (isset($this->cache[$objectName])) {
			return $this->cache[$objectName];
		}
		
		return null;
	}
	
	/**
	 * Returns a like object.
	 *
	 * @param	ObjectType	$objectType
	 * @param	integer		$objectID
	 * @return	LikeObject|null
	 */
	public function getLikeObject(ObjectType $objectType, $objectID) {
		if (!isset($this->likeObjectCache[$objectType->objectTypeID][$objectID])) {
			$this->loadLikeObjects($objectType, [$objectID]);
		}
		
		return isset($this->likeObjectCache[$objectType->objectTypeID][$objectID]) ? $this->likeObjectCache[$objectType->objectTypeID][$objectID] : null;
	}
	
	/**
	 * Returns the like objects of a specific object type.
	 *
	 * @param	ObjectType	$objectType
	 * @return	LikeObject[]
	 */
	public function getLikeObjects(ObjectType $objectType) {
		if (isset($this->likeObjectCache[$objectType->objectTypeID])) {
			return $this->likeObjectCache[$objectType->objectTypeID];
		}
		
		return [];
	}
	
	/**
	 * Loads the like data for a set of objects and returns the number of loaded
	 * like objects
	 *
	 * @param	ObjectType	$objectType
	 * @param	array		$objectIDs
	 * @return	integer
	 */
	public function loadLikeObjects(ObjectType $objectType, array $objectIDs) {
		if (empty($objectIDs)) {
			return 0;
		}
		
		$this->cacheLikeableObjects($objectType->objectType, $objectIDs);
		
		$i = 0;
		
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("like_object.objectTypeID = ?", [$objectType->objectTypeID]);
		$conditions->add("like_object.objectID IN (?)", [$objectIDs]);
		$parameters = $conditions->getParameters();
		
		if (WCF::getUser()->userID) {
			$sql = "SELECT		like_object.*,
						COALESCE(like_table.reactionTypeID, 0) AS reactionTypeID, 
						COALESCE(like_table.likeValue, 0) AS liked
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
	 * Add a reaction to an object. 
	 * 
	 * @param	ILikeObject	$likeable
	 * @param	User		$user
	 * @param	integer		$reactionTypeID
	 * @param 	integer		$time
	 * @return	array
	 */
	public function react(ILikeObject $likeable, User $user, $reactionTypeID, $time = TIME_NOW) {
		// verify if object is already liked by user
		$like = Like::getLike($likeable->getObjectType()->objectTypeID, $likeable->getObjectID(), $user->userID);
		
		// get like object
		$likeObject = LikeObject::getLikeObject($likeable->getObjectType()->objectTypeID, $likeable->getObjectID());
		
		// if vote is identically just revert the vote
		if ($like->likeID && ($like->reactionTypeID == $reactionTypeID)) {
			return $this->revertReact($like, $likeable, $likeObject, $user);
		}
		
		$reaction = ReactionTypeCache::getInstance()->getReactionTypeByID($reactionTypeID);
		
		try {
			WCF::getDB()->beginTransaction();
			
			$likeObjectData = $this->updateLikeObject($likeable, $likeObject, $like, $reaction);
			
			// update owner's like counter
			$this->updateUsersLikeCounter($likeable, $likeObject, $like, $reaction);
			
			if (!$like->likeID) {
				// save like
				$like = LikeEditor::create([
					'objectID' => $likeable->getObjectID(),
					'objectTypeID' => $likeable->getObjectType()->objectTypeID,
					'objectUserID' => $likeable->getUserID() ?: null,
					'userID' => $user->userID,
					'time' => $time,
					'likeValue' => 1, 
					'reactionTypeID' => $reactionTypeID
				]);
				
				$parameters = [
					'likeable' => $likeable,
					'likeObject' => $likeObject,
					'like' => $like,
					'user' => $user,
					'reactionType' => $reaction,
					'time' => $time,
					'likeObjectData' => $likeObjectData
				];
				EventHandler::getInstance()->fireAction($this, 'createReaction', $parameters);
				
				if ($likeable->getUserID()) {
					UserActivityPointHandler::getInstance()->fireEvent('com.woltlab.wcf.like.activityPointEvent.receivedLikes', $like->likeID, $likeable->getUserID());
				}
			}
			else {
				$likeEditor = new LikeEditor($like);
				$likeEditor->update([
					'time' => $time,
					'likeValue' => 1,
					'reactionTypeID' => $reactionTypeID
				]);
				
				$parameters = [
					'likeable' => $likeable,
					'likeObject' => $likeObject,
					'like' => $like,
					'user' => $user,
					'reactionType' => $reaction,
					'time' => $time,
					'likeObjectData' => $likeObjectData
				];
				EventHandler::getInstance()->fireAction($this, 'updateReaction', $parameters);
				
				if ($likeable->getUserID()) {
					UserActivityPointHandler::getInstance()->removeEvents('com.woltlab.wcf.like.activityPointEvent.receivedLikes', [$likeable->getUserID() => 1]);
				}
			}
			
			// This interface should help to determine whether the plugin has been adapted to the API 5.2.
			// If a LikeableObject does not implement this interface, no notification will be sent, because
			// we assume, that the plugin has not been adapted to the new API. 
			if ($likeable instanceof IReactionObject) {
				$likeable->sendNotification($like);
			}
			
			// update object's like counter
			$likeable->updateLikeCounter($likeObjectData['cumulativeLikes']);
			
			// update recent activity
			if (UserActivityEventHandler::getInstance()->getObjectTypeID($likeable->getObjectType()->objectType.'.recentActivityEvent')) {
				$objectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.user.recentActivityEvent', $likeable->getObjectType()->objectType.'.recentActivityEvent');
				
				if ($objectType->supportsReactions) {
					if ($like->likeID) {
						UserActivityEventHandler::getInstance()->removeEvent($likeable->getObjectType()->objectType . '.recentActivityEvent', $likeable->getObjectID(), $user->userID);
					}
					
					UserActivityEventHandler::getInstance()->fireEvent($likeable->getObjectType()->objectType . '.recentActivityEvent', $likeable->getObjectID(), $likeable->getLanguageID(), $user->userID, TIME_NOW, ['reactionType' => $reaction]);
				}
			}
			
			WCF::getDB()->commitTransaction();
			
			return [
				'cachedReactions' => $likeObjectData['cachedReactions'], 
				'reactionTypeID' => $reactionTypeID, 
				'like' => $like,
				'likeObject' => $likeObjectData['likeObject'],
				'cumulativeLikes' => $likeObjectData['cumulativeLikes']
			];
		}
		catch (DatabaseQueryException $e) {
			WCF::getDB()->rollBackTransaction();
		}
		
		return [
			'cachedReactions' => [], 
		]; 
	}
	
	/**
	 * Creates or updates a LikeObject for an likable object. 
	 * 
	 * @param	ILikeObject	$likeable
	 * @param	LikeObject	$likeObject
	 * @param	Like		$like
	 * @param	ReactionType	$reactionType
	 * @return	array
	 */
	private function updateLikeObject(ILikeObject $likeable, LikeObject $likeObject, Like $like, ReactionType $reactionType) {
		// update existing object
		if ($likeObject->likeObjectID) {
			$cumulativeLikes = $likeObject->cumulativeLikes;
			
			if ($likeObject->cachedReactions !== null) {
				$cachedReactions = @unserialize($likeObject->cachedReactions);
			}
			else {
				$cachedReactions = [];
			}
			
			if (!is_array($cachedReactions)) {
				$cachedReactions = [];
			}
			
			if ($like->likeID) {
				$cumulativeLikes--;
				
				if (isset($cachedReactions[$like->getReactionType()->reactionTypeID])) {
					if (--$cachedReactions[$like->getReactionType()->reactionTypeID] == 0) {
						unset($cachedReactions[$like->getReactionType()->reactionTypeID]);
					}
				}
			}
			
			$cumulativeLikes++;
			
			if (isset($cachedReactions[$reactionType->reactionTypeID])) {
				$cachedReactions[$reactionType->reactionTypeID]++;
			}
			else {
				$cachedReactions[$reactionType->reactionTypeID] = 1;
			}
			
			$cachedReactions = self::cleanUpCachedReactions($cachedReactions);
			
			// build update date
			$updateData = [
				'likes' => $cumulativeLikes,
				'dislikes' => 0,
				'cumulativeLikes' => $cumulativeLikes,
				'cachedReactions' => serialize($cachedReactions),
			];
			
			// update data
			$likeObjectEditor = new LikeObjectEditor($likeObject);
			$likeObjectEditor->update($updateData);
		}
		else {
			$cumulativeLikes = 1;
			$cachedReactions = [
				$reactionType->reactionTypeID => 1,
			];
			
			// create cache
			$likeObject = LikeObjectEditor::create([
				'objectTypeID' => $likeable->getObjectType()->objectTypeID,
				'objectID' => $likeable->getObjectID(),
				'objectUserID' => $likeable->getUserID() ?: null,
				'likes' => $cumulativeLikes,
				'dislikes' => 0,
				'cumulativeLikes' => $cumulativeLikes,
				'cachedReactions' => serialize($cachedReactions),
			]);
		}
		
		$parameters = [
			'likeable' => $likeable,
			'likeObject' => $likeObject,
			'like' => $like,
			'reactionType' => $reactionType,
			'cumulativeLikes' => $cumulativeLikes,
			'cachedReactions' => $cachedReactions
		];
		EventHandler::getInstance()->fireAction($this, 'updateLikeObject', $parameters);
		
		return [
			'cumulativeLikes' => $parameters['cumulativeLikes'],
			'cachedReactions' => $parameters['cachedReactions'],
			'likeObject' => $parameters['likeObject'],
		]; 
	}
	
	/**
	 * Updates the like counter for a user. 
	 *
	 * @param	ILikeObject	$likeable
	 * @param	LikeObject	$likeObject
	 * @param	Like		$like
	 * @param	ReactionType	$reactionType
	 */
	private function updateUsersLikeCounter(ILikeObject $likeable, LikeObject $likeObject, Like $like, ReactionType $reactionType = null) {
		if ($likeable->getUserID()) {
			$likesReceived = 0;
			if ($like->likeID) {
				$likesReceived--;
			}
			
			if ($reactionType !== null) {
				$likesReceived++;
			}
			
			if ($likesReceived !== 0) {
				$userEditor = new UserEditor(UserRuntimeCache::getInstance()->getObject($likeable->getUserID()));
				$userEditor->updateCounters(['likesReceived' => $likesReceived]);
			}
			
			$parameters = [
				'likeable' => $likeable,
				'likeObject' => $likeObject,
				'like' => $like,
				'reactionType' => $reactionType
			];
			EventHandler::getInstance()->fireAction($this, 'updateUsersLikeCounter', $parameters);
		}
	}
	
	/**
	 * Reverts a reaction for an object. 
	 * 
	 * @param	Like		$like
	 * @param	ILikeObject	$likeable
	 * @param	LikeObject	$likeObject
	 * @param	User		$user
	 * @return	array
	 */
	public function revertReact(Like $like, ILikeObject $likeable, LikeObject $likeObject, User $user) {
		if (!$like->likeID) {
			throw new \InvalidArgumentException('The given parameter $like is invalid.');
		}
		
		try {
			WCF::getDB()->beginTransaction();
			
			$likeObjectData = $this->revertLikeObject($likeObject, $like);
			
			// update owner's like counter
			$this->updateUsersLikeCounter($likeable, $likeObject, $like, null);
			
			$likeEditor = new LikeEditor($like);
			$likeEditor->delete();
			
			if ($likeable->getUserID()) {
				UserActivityPointHandler::getInstance()->removeEvents('com.woltlab.wcf.like.activityPointEvent.receivedLikes', [$likeable->getUserID() => 1]);
			}
			
			// update object's like counter
			$likeable->updateLikeCounter($likeObjectData['cumulativeLikes']);
			
			$parameters = [
				'likeable' => $likeable,
				'likeObject' => $likeObject,
				'like' => $like,
				'user' => $user,
				'likeObjectData' => $likeObjectData
			];
			EventHandler::getInstance()->fireAction($this, 'revertReact', $parameters);
			
			// delete recent activity
			if (UserActivityEventHandler::getInstance()->getObjectTypeID($likeable->getObjectType()->objectType.'.recentActivityEvent')) {
				UserActivityEventHandler::getInstance()->removeEvent($likeable->getObjectType()->objectType.'.recentActivityEvent', $likeable->getObjectID(), $user->userID);
			}
			
			WCF::getDB()->commitTransaction();
			
			return [
				'cachedReactions' => $likeObjectData['cachedReactions'],
				'reactionTypeID' => null,
				'likeObject' => $likeObjectData['likeObject'],
				'cumulativeLikes' => $likeObjectData['cumulativeLikes']
			];
		}
		catch (DatabaseQueryException $e) {
			WCF::getDB()->rollBackTransaction();
		}
		
		return [
			'cachedReactions' => [],
			'reactionTypeID' => null,
			'likeObject' => [],
			'cumulativeLikes' => null
		];
	}
	
	/**
	 * Creates or updates a LikeObject for an likable object.
	 *
	 * @param	LikeObject	$likeObject
	 * @param	Like		$like
	 * @return	array
	 */
	private function revertLikeObject(LikeObject $likeObject, Like $like) {
		if (!$likeObject->likeObjectID) {
			throw new \InvalidArgumentException('The given parameter $likeObject is invalid.');
		}
		
		// update existing object
		$cumulativeLikes = $likeObject->cumulativeLikes;
		$cachedReactions = @unserialize($likeObject->cachedReactions);
		if (!is_array($cachedReactions)) {
			$cachedReactions = [];
		}
		
		if ($like->likeID) {
			$cumulativeLikes--;
			
			if (isset($cachedReactions[$like->getReactionType()->reactionTypeID])) {
				if (--$cachedReactions[$like->getReactionType()->reactionTypeID] == 0) {
					unset($cachedReactions[$like->getReactionType()->reactionTypeID]);
				}
			}
			
			$cachedReactions = self::cleanUpCachedReactions($cachedReactions);
			
			// build update date
			$updateData = [
				'likes' => $cumulativeLikes,
				'dislikes' => 0,
				'cumulativeLikes' => $cumulativeLikes,
				'cachedReactions' => serialize($cachedReactions)
			];
			
			// update data
			$likeObjectEditor = new LikeObjectEditor($likeObject);
			$likeObjectEditor->update($updateData);
		}
		
		$parameters = [
			'likeObject' => $likeObject,
			'like' => $like,
			'cachedReactions' => $cachedReactions,
			'cumulativeLikes' => $cumulativeLikes
		];
		EventHandler::getInstance()->fireAction($this, 'revertLikeObject', $parameters);
		
		return [
			'cumulativeLikes' => $parameters['cumulativeLikes'],
			'cachedReactions' => $parameters['cachedReactions'],
			'likeObject' => $parameters['likeObject']
		];
	}
	
	/**
	 * Removes all reactions for given objects.
	 *
	 * @param	string		$objectType
	 * @param	integer[]	$objectIDs
	 * @param	string[]	$notificationObjectTypes
	 */
	public function removeReactions($objectType, array $objectIDs, array $notificationObjectTypes = []) {
		$objectTypeObj = $this->getObjectType($objectType);
		
		if ($objectTypeObj === null) {
			throw new \InvalidArgumentException('Given objectType is invalid.');
		}
		
		// get like objects
		$likeObjectList = new LikeObjectList();
		$likeObjectList->getConditionBuilder()->add('like_object.objectTypeID = ?', [$objectTypeObj->objectTypeID]);
		$likeObjectList->getConditionBuilder()->add('like_object.objectID IN (?)', [$objectIDs]);
		$likeObjectList->readObjects();
		$likeObjects = $likeObjectList->getObjects();
		$likeObjectIDs = $likeObjectList->getObjectIDs();
		
		// reduce count of received users
		$users = [];
		foreach ($likeObjects as $likeObject) {
			if ($likeObject->likes) {
				if (!isset($users[$likeObject->objectUserID])) {
					$users[$likeObject->objectUserID] = 0;
				}
				
				$users[$likeObject->objectUserID] -= count($likeObject->getReactions());
			}
		}
		
		foreach ($users as $userID => $reactionData) {
			$userEditor = new UserEditor(new User(null, ['userID' => $userID]));
			$userEditor->updateCounters([
				'likesReceived' => $users[$userID],
			]);
		}
		
		// get like ids
		$likeList = new LikeList();
		$likeList->getConditionBuilder()->add('like_table.objectTypeID = ?', [$objectTypeObj->objectTypeID]);
		$likeList->getConditionBuilder()->add('like_table.objectID IN (?)', [$objectIDs]);
		$likeList->readObjects();
		
		if (count($likeList)) {
			$likeData = [];
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
		
		$parameters = [
			'objectType' => $objectTypeObj,
			'objectIDs' => $objectIDs,
			'notificationObjectTypes' => $notificationObjectTypes,
			'likeObjectIDs' => $likeObjectIDs,
			'likeObjects' => $likeObjects
		];
		EventHandler::getInstance()->fireAction($this, 'removeReactions', $parameters);
		
		// delete activity events
		if (UserActivityEventHandler::getInstance()->getObjectTypeID($objectTypeObj->objectType.'.recentActivityEvent')) {
			UserActivityEventHandler::getInstance()->removeEvents($objectTypeObj->objectType.'.recentActivityEvent', $objectIDs);
		}
	}
	
	/**
	 * Returns current like object status.
	 *
	 * @param	LikeObject	$likeObject
	 * @param	User		$user
	 * @return	array
	 */
	protected function loadLikeStatus(LikeObject $likeObject, User $user) {
		$sql = "SELECT		like_object.likes, like_object.dislikes, like_object.cumulativeLikes,
					COALESCE(like_table.reactionTypeID, 0) AS reactionTypeID,
					COALESCE(like_table.likeValue, 0) AS liked
			FROM		wcf".WCF_N."_like_object like_object
			LEFT JOIN	wcf".WCF_N."_like like_table
			ON		(like_table.objectTypeID = ?
					AND like_table.objectID = like_object.objectID
					AND like_table.userID = ?)
			WHERE		like_object.likeObjectID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			$likeObject->objectTypeID,
			$user->userID,
			$likeObject->likeObjectID
		]);
		
		return $statement->fetchArray();
	}
	
	/**
	 * Returns the first available reaction type.
	 * 
	 * @return ReactionType|null
	 */
	public function getFirstReactionType() {
		static $firstReactionType;
		
		if ($firstReactionType === null) {
			$reactionTypes = ReactionTypeCache::getInstance()->getReactionTypes();
			ReactionType::sort($reactionTypes, 'showOrder');
			
			$firstReactionType = reset($reactionTypes);
		}
		
		return $firstReactionType;
	}
	
	/**
	 * Returns the first available reaction type's id.
	 * 
	 * @return int|null
	 */
	public function getFirstReactionTypeID() {
		$firstReactionType = $this->getFirstReactionType();
		
		return $firstReactionType ? $firstReactionType->reactionTypeID : null;
	}
	
	/**
	 * Removes deleted reactions from the reaction counter for the like object table. 
	 * 
	 * @param       array   $cachedReactions
	 * @return      array
	 */
	private function cleanUpCachedReactions(array $cachedReactions) {
		foreach ($cachedReactions as $reactionTypeID => $count) {
			if (self::getReactionTypeByID($reactionTypeID) === null) {
				unset($cachedReactions[$reactionTypeID]);
			}
		}
		
		return $cachedReactions;
	}
	
	/**
	 * @param string|null $cachedReactions
	 * @return array|null
	 * @since 5.2
	 */
	public function getTopReaction($cachedReactions) {
		if ($cachedReactions) {
			$cachedReactions = @unserialize($cachedReactions);
			
			if (is_array($cachedReactions)) {
				$cachedReactions = self::cleanUpCachedReactions($cachedReactions);
				
				if (!empty($cachedReactions)) {
					$allReactions = array_sum($cachedReactions);
					
					arsort($cachedReactions, SORT_NUMERIC);
					
					$count = current($cachedReactions);
					return [
						'count' => $count,
						'other' => $allReactions - $count,
						'reaction' => ReactionTypeCache::getInstance()->getReactionTypeByID(key($cachedReactions)),
					];
				}
			}
		}
		
		return null;
	}
}
