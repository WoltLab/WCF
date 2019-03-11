<?php
namespace wcf\data\reaction;
use wcf\data\like\ILikeObjectTypeProvider;
use wcf\data\like\IRestrictedLikeObjectTypeProvider;
use wcf\data\like\Like;
use wcf\data\like\LikeEditor;
use wcf\data\like\object\ILikeObject;
use wcf\data\like\object\LikeObjectEditor;
use wcf\data\like\ViewableLikeList;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\reaction\type\ReactionType;
use wcf\data\reaction\type\ReactionTypeCache;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\user\User;
use wcf\data\user\UserEditor;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\reaction\ReactionHandler;
use wcf\system\user\activity\point\UserActivityPointHandler;
use wcf\system\user\GroupedUserList;
use wcf\system\WCF;

/**
 * Executes reaction-related actions.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Reaction
 * @since	5.2
 * 
 * @method	Like		create()
 * @method	LikeEditor[]	getObjects()
 * @method	LikeEditor	getSingleObject()
 */
class ReactionAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $allowGuestAccess = ['getReactionDetails'];
	
	/**
	 * @inheritDoc
	 */
	protected $className = LikeEditor::class;
	
	/**
	 * likeable object
	 * @var	ILikeObject
	 */
	public $likeableObject = null;
	
	/**
	 * object type object
	 * @var	ObjectType
	 */
	public $objectType = null;
	
	/**
	 * like object type provider object
	 * @var	ILikeObjectTypeProvider
	 */
	public $objectTypeProvider = null;
	
	/**
	 * reaction type for the reaction
	 * @var	ReactionType
	 */
	public $reactionType = null;
	
	/**
	 * Validates parameters to fetch like details.
	 */
	public function validateGetReactionDetails() {
		$this->validateObjectParameters();
		
		if (!WCF::getSession()->getPermission('user.like.canViewLike')) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * Returns like details.
	 *
	 * @return	string[]
	 */
	public function getReactionDetails() {
		$likeList = new ViewableLikeList();
		$likeList->getConditionBuilder()->add('objectID = ?', [$this->parameters['data']['objectID']]);
		$likeList->getConditionBuilder()->add('objectTypeID = ?', [$this->objectType->objectTypeID]);
		$likeList->sqlOrderBy = 'time DESC';
		$likeList->readObjects();
		
		$data = [];
		foreach ($likeList as $item) {
			if ($item->getReactionType()->isDisabled) continue; 
			
			// we cast the reactionTypeID to a string, so that we can sort the array
			if (!isset($data[(string)$item->getReactionType()->reactionTypeID])) {
				$data[(string)$item->getReactionType()->reactionTypeID] = new GroupedUserList($item->getReactionType()->getTitle());
			}
			
			$data[(string)$item->getReactionType()->reactionTypeID]->addUserIDs([$item->userID]);
		}
		
		GroupedUserList::loadUsers();
		
		uksort($data, function ($a, $b) {
			$sortOrderA = ReactionTypeCache::getInstance()->getReactionTypeByID($a)->showOrder;
			$sortOrderB = ReactionTypeCache::getInstance()->getReactionTypeByID($b)->showOrder;
			
			return ($sortOrderA < $sortOrderB) ? -1 : 1;
			
		});
		
		return [
			'template' => WCF::getTPL()->fetch('groupedUserList', 'wcf', ['groupedUsers' => $data]),
			'title' => WCF::getLanguage()->get('wcf.reactions.summary.title')
		];
	}
	
	/**
	 * Validates permissions for given object.
	 */
	protected function validateObjectParameters() {
		if (!MODULE_LIKE) {
			throw new IllegalLinkException();
		}
		
		$this->readString('containerID', false, 'data');
		$this->readInteger('objectID', false, 'data');
		$this->readString('objectType', false, 'data');
		
		$this->objectType = ReactionHandler::getInstance()->getObjectType($this->parameters['data']['objectType']);
		if ($this->objectType === null) {
			throw new UserInputException('objectType');
		}
		
		$this->objectTypeProvider = $this->objectType->getProcessor();
		$this->likeableObject = $this->objectTypeProvider->getObjectByID($this->parameters['data']['objectID']);
		$this->likeableObject->setObjectType($this->objectType);
		if ($this->objectTypeProvider instanceof IRestrictedLikeObjectTypeProvider) {
			if (!$this->objectTypeProvider->canViewLikes($this->likeableObject)) {
				throw new PermissionDeniedException();
			}
		}
		else if (!$this->objectTypeProvider->checkPermissions($this->likeableObject)) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * React on an given object with the given reactionType. 
	 * 
	 * @return array
	 */
	public function react() {
		$reactionData = ReactionHandler::getInstance()->react($this->likeableObject, WCF::getUser(), $this->reactionType->reactionTypeID);
		
		// get stats
		return [
			'reactions' => $reactionData['cachedReactions'],
			'objectID' => $this->likeableObject->getObjectID(), 
			'objectType' => $this->parameters['data']['objectType'],
			'reactionTypeID' => $reactionData['reactionTypeID'],
			'containerID' => $this->parameters['data']['containerID'],
			'reputationCount' => $reactionData['cumulativeLikes']
		];
	}
	
	/**
	 * Validates the 'react' method.
	 */
	public function validateReact() {
		$this->validateObjectParameters();
		
		$this->readInteger('reactionTypeID', false);
		
		$this->reactionType = ReactionTypeCache::getInstance()->getReactionTypeByID($this->parameters['reactionTypeID']);
		
		if (!$this->reactionType->reactionTypeID || $this->reactionType->isDisabled) {
			throw new IllegalLinkException();
		}
		
		// check permissions
		if (!WCF::getUser()->userID || !WCF::getSession()->getPermission('user.like.canLike')) {
			throw new PermissionDeniedException();
		}
		
		// check if liking own content but forbidden by configuration
		$this->likeableObject = $this->objectTypeProvider->getObjectByID($this->parameters['data']['objectID']);
		$this->likeableObject->setObjectType($this->objectType);
		if (!LIKE_ALLOW_FOR_OWN_CONTENT && ($this->likeableObject->getUserID() == WCF::getUser()->userID)) {
			throw new PermissionDeniedException();
		}
		
		if ($this->objectTypeProvider instanceof IRestrictedLikeObjectTypeProvider) {
			if (!$this->objectTypeProvider->canLike($this->likeableObject)) {
				throw new PermissionDeniedException();
			}
		}
	}
	
	/**
	 * Validates parameters to load reactions.
	 */
	public function validateLoad() {
		$this->readInteger('lastLikeTime', true);
		$this->readInteger('userID');
		$this->readInteger('reactionTypeID');
		$this->readString('targetType');
	}
	
	/**
	 * Loads a list of reactions.
	 *
	 * @return	array
	 */
	public function load() {
		$likeList = new ViewableLikeList();
		if ($this->parameters['lastLikeTime']) {
			$likeList->getConditionBuilder()->add("like_table.time < ?", [$this->parameters['lastLikeTime']]);
		}
		if ($this->parameters['targetType'] == 'received') {
			$likeList->getConditionBuilder()->add("like_table.objectUserID = ?", [$this->parameters['userID']]);
		}
		else {
			$likeList->getConditionBuilder()->add("like_table.userID = ?", [$this->parameters['userID']]);
		}
		$likeList->getConditionBuilder()->add("like_table.reactionTypeID = ?", [$this->parameters['reactionTypeID']]);
		$likeList->readObjects();
		
		if (empty($likeList)) {
			return [];
		}
		
		// parse template
		WCF::getTPL()->assign([
			'likeList' => $likeList
		]);
		
		return [
			'lastLikeTime' => $likeList->getLastLikeTime(),
			'template' => WCF::getTPL()->fetch('userProfileLikeItem')
		];
	}
	
	/**
	 * Copies likes from one object id to another.
	 */
	public function copy() {
		$sourceObjectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.like.likeableObject', $this->parameters['sourceObjectType']);
		$targetObjectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.like.likeableObject', $this->parameters['targetObjectType']);
		
		//
		// step 1) get data
		//
		
		// get like object
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_like_object
			WHERE	objectTypeID = ?
				AND objectID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			$sourceObjectType->objectTypeID,
			$this->parameters['sourceObjectID']
		]);
		$row = $statement->fetchArray();
		
		// no reactions at all
		if ($row === false) {
			return;
		}
		
		unset($row['likeObjectID']);
		$row['objectTypeID'] = $targetObjectType->objectTypeID;
		$row['objectID'] = $this->parameters['targetObjectID'];
		$newLikeObject = LikeObjectEditor::create($row);
		
		//
		// step 2) copy
		//
		
		$sql = "INSERT INTO	wcf".WCF_N."_like
					(objectID, objectTypeID, objectUserID, userID, time, likeValue, reactionTypeID)
			SELECT		".$this->parameters['targetObjectID'].", ".$targetObjectType->objectTypeID.", objectUserID, userID, time, likeValue, reactionTypeID
			FROM		wcf".WCF_N."_like
			WHERE		objectTypeID = ?
					AND objectID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			$sourceObjectType->objectTypeID,
			$this->parameters['sourceObjectID']
		]);
		
		//
		// step 3) update owner
		//
		
		if ($newLikeObject->objectUserID) {
			$sql = "SELECT	COUNT(*) as count, like_table.reactionTypeID
				FROM	wcf".WCF_N."_like like_table
				WHERE	like_table.objectTypeID = ?
					AND like_table.objectID = ?
				GROUP BY like_table.reactionTypeID";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([
				$targetObjectType->objectTypeID,
				$this->parameters['targetObjectID']
			]);
			
			$updateValues = [
				'positive' => 0,
				'neutral' => 0, 
				'negative' => 0
			];
			while ($row = $statement->fetchArray()) {
				$reactionType = ReactionTypeCache::getInstance()->getReactionTypeByID($row['reactionTypeID']);
				
				if (!$reactionType->isDisabled) {
					if ($reactionType->isPositive()) {
						$updateValues['positive'] += $row['count'];
					}
					else if ($reactionType->isNegative()) {
						$updateValues['negative'] += $row['count'];
					}
					else if ($reactionType->isNeutral()) {
						$updateValues['neutral'] += $row['count'];
					}
				}
			}
			
			// update received likes
			$userEditor = new UserEditor(new User($newLikeObject->objectUserID));
			$userEditor->updateCounters([
				'likesReceived' => $updateValues['positive'],
				'positiveReactionsReceived' => $updateValues['positive'],
				'negativeReactionsReceived' => $updateValues['negative'],
				'neutralReactionsReceived' => $updateValues['neutral']
			]);
			
			// add activity points
			UserActivityPointHandler::getInstance()->fireEvents('com.woltlab.wcf.like.activityPointEvent.receivedLikes', [$newLikeObject->objectUserID => $updateValues['positive']]);
		}
	}
}
