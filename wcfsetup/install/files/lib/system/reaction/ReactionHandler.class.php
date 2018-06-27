<?php
declare(strict_types=1);
namespace wcf\system\reaction;
use wcf\data\like\ILikeObjectTypeProvider;
use wcf\data\like\object\ILikeObject;
use wcf\data\like\object\LikeObject;
use wcf\data\like\object\LikeObjectEditor;
use wcf\data\like\Like;
use wcf\data\like\LikeEditor;
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
use wcf\system\WCF;
use wcf\util\JSON;

/**
 * Handles the reactions of objects.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Reaction
 * @since       3.2
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
	public function getReactionsJSVariable(): string {
		$reactions = ReactionTypeCache::getInstance()->getEnabledReactionTypes();
		
		$returnValues = []; 
		
		foreach ($reactions as $reaction) {
			$returnValues[$reaction->reactionTypeID] = [
				'title' => $reaction->getTitle(), 
				'renderedIcon' => $reaction->renderIcon(), 
				'iconPath' => $reaction->getIconPath()
			];
		}
		
		return JSON::encode($returnValues);
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
	 * @param       string          $objectName
	 * @param       integer         $objectID
	 * @return      string
	 */
	public function getDataAttributes($objectName, $objectID): string {
		$objectType = $this->getObjectType($objectName);
		if ($objectType === null) {
			throw new \InvalidArgumentException("ObjectName '{$objectName}' is unknown for definition 'com.woltlab.wcf.like.likeableObject'.");
		}
		
		/** @var ILikeObjectTypeProvider $objectTypeProcessor */
		$objectTypeProcessor = $objectType->getProcessor();
		
		$object = $objectTypeProcessor->getObjectByID($objectID);
		
		if ($object === null) {
			throw new \InvalidArgumentException("Object with the object id '{$objectID}' for object type '{$objectName}' is unknown.");
		}
		
		if (!($object instanceof ILikeObject)) {
			throw new ImplementationException(get_class($object), ILikeObject::class);
		}
		
		$dataAttributes = [
			'object-id' => $object->getObjectID(),
			'object-type' => $objectName,
			'user-id' => $object->getUserID()
		];
		
		EventHandler::getInstance()->fireAction($this, 'getDataAttributes', $dataAttributes);
		
		$returnDataAttributes = '';
		
		foreach ($dataAttributes as $key => $value) {
			if (!empty($returnDataAttributes)) {
				$returnDataAttributes .= ' ';
			}
			
			$returnDataAttributes .= 'data-'. $key .'="'. $value .'"';
		}
		
		return $returnDataAttributes;
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
	public function getLikeObjects(ObjectType $objectType): array {
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
	public function loadLikeObjects(ObjectType $objectType, array $objectIDs): int {
		if (empty($objectIDs)) {
			return 0;
		}
		
		$i = 0;
		
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("like_object.objectTypeID = ?", [$objectType->objectTypeID]);
		$conditions->add("like_object.objectID IN (?)", [$objectIDs]);
		$parameters = $conditions->getParameters();
		
		if (WCF::getUser()->userID) {
			$sql = "SELECT		like_object.*,
						COALESCE(like_table.reactionTypeID, 0) AS reactionTypeID
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
	public function react(ILikeObject $likeable, User $user, $reactionTypeID, $time = TIME_NOW): array {
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
					'likeValue' => $reaction->type, 
					'reactionTypeID' => $reactionTypeID
				]);
				
				if ($reaction->isPositive() && $likeable->getUserID()) {
					UserActivityPointHandler::getInstance()->fireEvent('com.woltlab.wcf.like.activityPointEvent.receivedLikes', $like->likeID, $likeable->getUserID());
				}
			}
			else {
				$likeEditor = new LikeEditor($like);
				$likeEditor->update([
					'time' => $time,
					'likeValue' => $reaction->type,
					'reactionTypeID' => $reactionTypeID
				]);
				
				if ($likeable->getUserID()) {
					if ($like->getReactionType()->isPositive() && !$reaction->isPositive()) {
						UserActivityPointHandler::getInstance()->removeEvents('com.woltlab.wcf.like.activityPointEvent.receivedLikes', [$likeable->getUserID() => 1]);
					}
					else if (!$like->getReactionType()->isPositive() && $reaction->isPositive()) {
						UserActivityPointHandler::getInstance()->fireEvent('com.woltlab.wcf.like.activityPointEvent.receivedLikes', $like->likeID, $likeable->getUserID());
					}
				}
			}
			
			// This interface should help to determine whether the plugin has been adapted to the API 3.2.
			// If a LikeableObject does not implement this interface, no notification will be sent, because
			// we assume, that the plugin is adapted to the new API. 
			if ($likeable instanceof IReactionObject) {
				$likeable->sendNotification($like);
			}
			
			// update object's like counter
			$likeable->updateLikeCounter($likeObjectData['cumulativeLikes']);
			
			// update recent activity
			if (UserActivityEventHandler::getInstance()->getObjectTypeID($likeable->getObjectType()->objectType.'.recentActivityEvent')) {
				if ($like->likeID) {
					UserActivityEventHandler::getInstance()->removeEvent($likeable->getObjectType()->objectType.'.recentActivityEvent', $likeable->getObjectID(), $user->userID);
				}
				
				UserActivityEventHandler::getInstance()->fireEvent($likeable->getObjectType()->objectType.'.recentActivityEvent', $likeable->getObjectID(), $likeable->getLanguageID(), $user->userID, TIME_NOW, [
					'reactionType' => $reaction
				]);
			}
			
			WCF::getDB()->commitTransaction();
			
			return [
				'cachedReactions' => $likeObjectData['cachedReactions'], 
				'reactionTypeID' => $reactionTypeID
			];
		}
		catch (DatabaseQueryException $e) {
			WCF::getDB()->rollBackTransaction();
		}
		
		// @TODO return some dummy values
		return [
			'cachedReactions' => [], 
			'reactionTypeID' => null
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
	private function updateLikeObject(ILikeObject $likeable, LikeObject $likeObject, Like $like, ReactionType $reactionType): array {
		// update existing object
		if ($likeObject->likeObjectID) {
			$likes = $likeObject->likes;
			$dislikes = $likeObject->dislikes;
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
				if ($like->getReactionType()->isPositive()) {
					$likes--;
					$cumulativeLikes--;
				}
				else if ($like->getReactionType()->isNegative()) {
					$dislikes--;
					$cumulativeLikes++;
				}
				
				if (isset($cachedReactions[$like->getReactionType()->reactionTypeID])) {
					if (--$cachedReactions[$like->getReactionType()->reactionTypeID] == 0) {
						unset($cachedReactions[$like->getReactionType()->reactionTypeID]);
					}
				}
			}
			
			if ($reactionType->isPositive()) {
				$likes++;
				$cumulativeLikes++;
			}
			else if ($reactionType->isNegative()) {
				$dislikes++;
				$cumulativeLikes--;
			}
			
			if (isset($cachedReactions[$reactionType->reactionTypeID])) {
				$cachedReactions[$reactionType->reactionTypeID]++;
			}
			else {
				$cachedReactions[$reactionType->reactionTypeID] = 1;
			}
			
			// build update date
			$updateData = [
				'likes' => $likes,
				'dislikes' => $dislikes,
				'cumulativeLikes' => $cumulativeLikes,
				'cachedReactions' => serialize($cachedReactions)
			];
			
			// update data
			$likeObjectEditor = new LikeObjectEditor($likeObject);
			$likeObjectEditor->update($updateData);
		}
		else {
			$cumulativeLikes = $reactionType->type;
			$cachedReactions = [
				$reactionType->reactionTypeID => 1
			];
			
			// create cache
			LikeObjectEditor::create([
				'objectTypeID' => $likeable->getObjectType()->objectTypeID,
				'objectID' => $likeable->getObjectID(),
				'objectUserID' => $likeable->getUserID() ?: null,
				'likes' => ($reactionType->isPositive()) ? 1 : 0,
				'dislikes' => ($reactionType->isNegative()) ? 1 : 0,
				'cumulativeLikes' => $cumulativeLikes,
				'cachedReactions' => serialize($cachedReactions)
			]);
		}
		
		return [
			'cumulativeLikes' => $cumulativeLikes, 
			'cachedReactions' => $cachedReactions
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
			$counters = [
				'likesReceived' => 0,
				'positiveReactionsReceived' => 0,
				'negativeReactionsReceived' => 0,
				'neutralReactionsReceived' => 0
			];
			
			if ($like->likeID) {
				if ($like->getReactionType()->isPositive()) {
					$counters['likesReceived']--;
					$counters['positiveReactionsReceived']--;
				}
				else if ($like->getReactionType()->isNegative()) {
					$counters['negativeReactionsReceived']--;
				}
				else if ($like->getReactionType()->isNeutral()) {
					$counters['neutralReactionsReceived']--;
				}
			}
			
			if ($reactionType !== null) {
				if ($reactionType->isPositive()) {
					$counters['likesReceived']++;
					$counters['positiveReactionsReceived']++;
				}
				else if ($reactionType->isNegative()) {
					$counters['negativeReactionsReceived']++;
				}
				else if ($reactionType->isNeutral()) {
					$counters['neutralReactionsReceived']++;
				}
			}
			
			$userEditor = new UserEditor(UserRuntimeCache::getInstance()->getObject($likeable->getUserID()));
			$userEditor->updateCounters($counters);
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
	public function revertReact(Like $like, ILikeObject $likeable, LikeObject $likeObject, User $user): array {
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
			
			if ($likeable->getUserID() && $like->getReactionType()->isPositive()) {
				UserActivityPointHandler::getInstance()->removeEvents('com.woltlab.wcf.like.activityPointEvent.receivedLikes', [$likeable->getUserID() => 1]);
			}
			
			// update object's like counter
			$likeable->updateLikeCounter($likeObjectData['cumulativeLikes']);
			
			// delete recent activity
			if (UserActivityEventHandler::getInstance()->getObjectTypeID($likeable->getObjectType()->objectType.'.recentActivityEvent')) {
				UserActivityEventHandler::getInstance()->removeEvent($likeable->getObjectType()->objectType.'.recentActivityEvent', $likeable->getObjectID(), $user->userID);
			}
			
			WCF::getDB()->commitTransaction();
			
			return [
				'cachedReactions' => $likeObjectData['cachedReactions'],
				'reactionTypeID' => null
			];
		}
		catch (DatabaseQueryException $e) {
			WCF::getDB()->rollBackTransaction();
		}
		
		// @TODO return some dummy values
		return [
			'cachedReactions' => [],
			'reactionTypeID' => null
		];
	}
	
	/**
	 * Creates or updates a LikeObject for an likable object.
	 *
	 * @param	LikeObject	$likeObject
	 * @param	Like		$like
	 * @return	array
	 */
	private function revertLikeObject(LikeObject $likeObject, Like $like): array {
		if (!$likeObject->likeObjectID) {
			throw new \InvalidArgumentException('The given parameter $likeObject is invalid.');
		}
		
		// update existing object
		$likes = $likeObject->likes;
		$dislikes = $likeObject->dislikes;
		$cumulativeLikes = $likeObject->cumulativeLikes;
		$cachedReactions = @unserialize($likeObject->cachedReactions);
		if (!is_array($cachedReactions)) {
			$cachedReactions = [];
		}
		
		if ($like->likeID) {
			if ($like->getReactionType()->isPositive()) {
				$likes--;
				$cumulativeLikes--;
			}
			else if ($like->getReactionType()->isNegative()) {
				$dislikes--;
				$cumulativeLikes++;
			}
			
			if (isset($cachedReactions[$like->getReactionType()->reactionTypeID])) {
				if (--$cachedReactions[$like->getReactionType()->reactionTypeID] == 0) {
					unset($cachedReactions[$like->getReactionType()->reactionTypeID]);
				}
			}
			
			// build update date
			$updateData = [
				'likes' => $likes,
				'dislikes' => $dislikes,
				'cumulativeLikes' => $cumulativeLikes,
				'cachedReactions' => serialize($cachedReactions)
			];
			
			// update data
			$likeObjectEditor = new LikeObjectEditor($likeObject);
			$likeObjectEditor->update($updateData);
		}
		
		return [
			'cumulativeLikes' => $cumulativeLikes,
			'cachedReactions' => $cachedReactions
		];
	}
	
	public function removeReacts($objectType, array $objectIDs, array $notificationObjectTypes = []) {
		// @TODO
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
					COALESCE(like_table.reactionTypeID, 0) AS reactionTypeID
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
}
