<?php
namespace wcf\data\reaction;
use wcf\data\like\IRestrictedLikeObjectTypeProvider;
use wcf\data\like\LikeEditor;
use wcf\data\like\ViewableLikeList;
use wcf\data\reaction\type\ReactionType;
use wcf\data\reaction\type\ReactionTypeCache;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\reaction\ReactionHandler;
use wcf\system\WCF;

/**
 * Executes reaction-related actions.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Reaction
 * @since	3.2
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
	 * @var	\wcf\data\like\object\ILikeObject
	 */
	public $likeableObject = null;
	
	/**
	 * object type object
	 * @var	\wcf\data\object\type\ObjectType
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
		
		$this->readInteger('reactionTypeID');
		$this->readInteger('pageNo');
		
		$this->reactionType = ReactionTypeCache::getInstance()->getReactionTypeByID($this->parameters['reactionTypeID']); 
		
		if ($this->reactionType === null) {
			throw new IllegalLinkException();
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
		$likeList->getConditionBuilder()->add('reactionTypeID = ?', [$this->reactionType->reactionTypeID]);
		$likeList->sqlLimit = 25;
		$likeList->sqlOffset = ($this->parameters['pageNo'] - 1) * 10;
		$likeList->sqlOrderBy = 'time DESC';
		$pageCount = ceil($likeList->countObjects() / 10);
		$likeList->readObjects();
		
		WCF::getTPL()->assign([
			'reactionUserList' => $likeList->getObjects(),
			'reactions' => ReactionTypeCache::getInstance()->getEnabledReactionTypes(), 
			'reactionTypeID' => $this->reactionType->reactionTypeID
		]);
		
		return [
			'reactionTypeID' => $this->reactionType->reactionTypeID,
			'pageNo' => $this->parameters['pageNo'],
			'pageCount' => $pageCount,
			'template' => WCF::getTPL()->fetch('reactionTabbedUserList'),
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
		
		if (!count($likeList)) {
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
}
