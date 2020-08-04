<?php
namespace wcf\data\user\ignore;
use wcf\data\user\follow\UserFollow;
use wcf\data\user\follow\UserFollowEditor;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * Executes ignored user-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User\Ignore
 * 
 * @method	UserIgnore		create()
 * @method	UserIgnoreEditor[]	getObjects()
 * @method	UserIgnoreEditor	getSingleObject()
 */
class UserIgnoreAction extends AbstractDatabaseObjectAction {
	/**
	 * Validates the 'ignore' action.
	 */
	public function validateIgnore() {
		$this->readInteger('userID', false, 'data');
		
		$userProfile = UserProfileRuntimeCache::getInstance()->getObject($this->parameters['data']['userID']);
		if ($userProfile === null || $userProfile->userID == WCF::getUser()->userID) {
			throw new IllegalLinkException();
		}
		
		// check permissions
		if ($userProfile->getPermission('user.profile.cannotBeIgnored')) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * Ignores a user.
	 * 
	 * @return	array
	 */
	public function ignore() {
		/** @var UserIgnore $ignore */
		$ignore = UserIgnoreEditor::createOrIgnore([
			'ignoreUserID' => $this->parameters['data']['userID'],
			'time' => TIME_NOW,
			'userID' => WCF::getUser()->userID,
		]);
		
		if ($ignore !== null) {
			UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'ignoredUserIDs');
			UserStorageHandler::getInstance()->reset([$this->parameters['data']['userID']], 'ignoredByUserIDs');
			
			// check if target user is following the current user
			$sql = "SELECT	*
				FROM	wcf".WCF_N."_user_follow
				WHERE	userID = ?
					AND followUserID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([
				$this->parameters['data']['userID'],
				WCF::getUser()->userID
			]);
			
			$follow = $statement->fetchObject(UserFollow::class);
			
			// remove follower
			if ($follow !== null) {
				$followEditor = new UserFollowEditor($follow);
				$followEditor->delete();
				
				// reset storage
				UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'followerUserIDs');
				UserStorageHandler::getInstance()->reset([$this->parameters['data']['userID']], 'followingUserIDs');
			}
		}
		
		return ['isIgnoredUser' => 1];
	}
	
	/**
	 * Validates the 'unignore' action.
	 */
	public function validateUnignore() {
		$this->readInteger('userID', false, 'data');
		
		$userProfile = UserProfileRuntimeCache::getInstance()->getObject($this->parameters['data']['userID']);
		if ($userProfile === null) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * Unignores a user.
	 * 
	 * @return	array
	 */
	public function unignore() {
		$ignore = UserIgnore::getIgnore($this->parameters['data']['userID']);
		
		if ($ignore->ignoreID) {
			$ignoreEditor = new UserIgnoreEditor($ignore);
			$ignoreEditor->delete();
			
			UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'ignoredUserIDs');
		}
		
		return ['isIgnoredUser' => 0];
	}
	
	/**
	 * @inheritDoc
	 */
	public function validateDelete() {
		// read objects
		if (empty($this->objects)) {
			$this->readObjects();
			
			if (empty($this->objects)) {
				throw new UserInputException('objectIDs');
			}
		}
		
		// validate ownership
		foreach ($this->getObjects() as $ignore) {
			if ($ignore->userID != WCF::getUser()->userID) {
				throw new PermissionDeniedException();
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function delete() {
		$returnValues = parent::delete();
		
		// reset storage
		UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'ignoredUserIDs');
		
		return $returnValues;
	}
}
