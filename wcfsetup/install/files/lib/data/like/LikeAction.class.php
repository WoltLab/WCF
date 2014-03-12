<?php
namespace wcf\data\like;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\like\LikeHandler;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\user\GroupedUserList;
use wcf\system\WCF;

/**
 * Executes like-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.like
 * @category	Community Framework
 */
class LikeAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$allowGuestAccess
	 */
	protected $allowGuestAccess = array('getLikeDetails');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\like\LikeEditor';
	
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
	 * @var	\wcf\data\like\ILikeObjectTypeProvider
	 */
	public $objectTypeProvider = null;
	
	/**
	 * Validates parameters to fetch like details.
	 */
	public function validateGetLikeDetails() {
		$this->validateObjectParameters();
	}
	
	/**
	 * Returns like details.
	 * 
	 * @return	array<string>
	 */
	public function getLikeDetails() {
		$sql = "SELECT		userID, likeValue
			FROM		wcf".WCF_N."_like
			WHERE		objectID = ?
					AND objectTypeID = ?
			ORDER BY	time DESC";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$this->parameters['data']['objectID'],
			$this->objectType->objectTypeID
		));
		$data = array(
			Like::LIKE => array(),
			Like::DISLIKE => array()
		);
		while ($row = $statement->fetchArray()) {
			$data[$row['likeValue']][] = $row['userID'];
		}
		
		$values = array();
		if (!empty($data[Like::LIKE])) {
			$values[Like::LIKE] = new GroupedUserList(WCF::getLanguage()->get('wcf.like.details.like'));
			$values[Like::LIKE]->addUserIDs($data[Like::LIKE]);
		}
		if (!empty($data[Like::DISLIKE])) {
			$values[Like::DISLIKE] = new GroupedUserList(WCF::getLanguage()->get('wcf.like.details.dislike'));
			$values[Like::DISLIKE]->addUserIDs($data[Like::DISLIKE]);
		}
		
		// load user profiles
		GroupedUserList::loadUsers();
		
		WCF::getTPL()->assign(array(
			'groupedUsers' => $values
		));
		
		return array(
			'containerID' => $this->parameters['data']['containerID'],
			'template' => WCF::getTPL()->fetch('groupedUserList')
		);
	}
	
	/**
	 * Validates parameters for like-related actions.
	 */
	public function validateLike() {
		$this->validateObjectParameters();
		
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
	}
	
	/**
	 * @see	\wcf\data\like\LikeAction::updateLike()
	 */
	public function like() {
		return $this->updateLike(Like::LIKE);
	}
	
	/**
	 * @see	\wcf\data\like\LikeAction::validateLike()
	 */
	public function validateDislike() {
		$this->validateLike();
	}
	
	/**
	 * @see	\wcf\data\like\LikeAction::updateLike()
	 */
	public function dislike() {
		return $this->updateLike(Like::DISLIKE);
	}
	
	/**
	 * Sets like/dislike for an object, executing this method again with the same parameters
	 * will revert the status (removing like/dislike).
	 * 
	 * @return	array
	 */
	protected function updateLike($likeValue) {
		$likeData = LikeHandler::getInstance()->like($this->likeableObject, WCF::getUser(), $likeValue);
		
		// handle activity event
		if (UserActivityEventHandler::getInstance()->getObjectTypeID($this->objectType->objectType.'.recentActivityEvent')) {
			if ($likeData['data']['liked'] == 1) {
				UserActivityEventHandler::getInstance()->fireEvent($this->objectType->objectType.'.recentActivityEvent', $this->parameters['data']['objectID']);
			}
			else {
				UserActivityEventHandler::getInstance()->removeEvents($this->objectType->objectType.'.recentActivityEvent', array($this->parameters['data']['objectID']));
			}
		}
		
		// get stats
		return array(
			'likes' => ($likeData['data']['likes'] === null) ? 0 : $likeData['data']['likes'],
			'dislikes' => ($likeData['data']['dislikes'] === null) ? 0 : $likeData['data']['dislikes'],
			'cumulativeLikes' => ($likeData['data']['cumulativeLikes'] === null) ? 0 : $likeData['data']['cumulativeLikes'],
			'isLiked' => ($likeData['data']['liked'] == 1) ? 1 : 0,
			'isDisliked' => ($likeData['data']['liked'] == -1) ? 1 : 0,
			'containerID' => $this->parameters['data']['containerID'],
			'newValue' => $likeData['newValue'],
			'oldValue' => $likeData['oldValue'],
			'users' => $likeData['users']
		);
	}
	
	/**
	 * Validates permissions for given object.
	 */
	protected function validateObjectParameters() {
		if (!MODULE_LIKE) {
			throw new PermissionDeniedException();
		}
		
		$this->readString('containerID', false, 'data');
		$this->readInteger('objectID', false, 'data');
		$this->readString('objectType', false, 'data');
		
		$this->objectType = LikeHandler::getInstance()->getObjectType($this->parameters['data']['objectType']);
		if ($this->objectType === null) {
			throw new UserInputException('objectType');
		}
		
		$this->objectTypeProvider = $this->objectType->getProcessor();
		$this->likeableObject = $this->objectTypeProvider->getObjectByID($this->parameters['data']['objectID']);
		$this->likeableObject->setObjectType($this->objectType);
		if (!$this->objectTypeProvider->checkPermissions($this->likeableObject)) {
			throw new PermissionDeniedException();
		}
	}
}
