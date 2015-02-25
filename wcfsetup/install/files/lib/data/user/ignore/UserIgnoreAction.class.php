<?php
namespace wcf\data\user\ignore;
use wcf\data\user\ignore\UserIgnore;
use wcf\data\user\ignore\UserIgnoreEditor;
use wcf\data\user\UserProfile;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * Executes ignored user-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.ignore
 * @category	Community Framework
 */
class UserIgnoreAction extends AbstractDatabaseObjectAction {
	/**
	 * Validates the 'ignore' action.
	 */
	public function validateIgnore() {
		$this->readInteger('ignoreUserID', false, 'data');
		
		$userProfile = UserProfile::getUserProfile($this->parameters['data']['ignoreUserID']);
		if ($userProfile === null || $userProfile->userID == WCF::getUser()->userID) {
			throw new IllegalLinkException();
		}
		
		// check permissions
		if ($userProfile->getPermission('user.profile.cannotBeIgnored')) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * Ignores an user.
	 * 
	 * @return	array
	 */
	public function ignore() {
		$ignore = UserIgnore::getIgnore($this->parameters['data']['ignoreUserID']);
		
		if (!$ignore->ignoreID) {
			UserIgnoreEditor::create(array(
				'ignoreUserID' => $this->parameters['data']['ignoreUserID'],
				'time' => TIME_NOW,
				'userID' => WCF::getUser()->userID,
			));
			
			UserStorageHandler::getInstance()->reset(array(WCF::getUser()->userID), 'ignoredUserIDs');
		}
		
		return array('isIgnoredUser' => 1);
	}
	
	/**
	 * Validates the 'unignore' action.
	 */
	public function validateUnignore() {
		$this->readInteger('ignoreUserID', false, 'data');
		
		$userProfile = UserProfile::getUserProfile($this->parameters['data']['ignoreUserID']);
		if ($userProfile === null) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * Unignores an user.
	 * 
	 * @return	array
	 */
	public function unignore() {
		$ignore = UserIgnore::getIgnore($this->parameters['data']['ignoreUserID']);
		
		if ($ignore->ignoreID) {
			$ignoreEditor = new UserIgnoreEditor($ignore);
			$ignoreEditor->delete();
			
			UserStorageHandler::getInstance()->reset(array(WCF::getUser()->userID), 'ignoredUserIDs');
		}
		
		return array('isIgnoredUser' => 0);
	}
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::validateDelete()
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
		foreach ($this->objects as $ignore) {
			if ($ignore->userID != WCF::getUser()->userID) {
				throw new PermissionDeniedException();
			}
		}
	}
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::delete()
	 */
	public function delete() {
		$returnValues = parent::delete();
		
		// reset storage
		UserStorageHandler::getInstance()->reset(array(WCF::getUser()->userID), 'ignoredUserIDs');
		
		return $returnValues;
	}
}
