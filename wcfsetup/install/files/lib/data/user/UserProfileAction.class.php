<?php
namespace wcf\data\user;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\bbcode\BBCodeParser;
use wcf\system\bbcode\MessageParser;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\option\user\UserOptionHandler;
use wcf\system\user\group\assignment\UserGroupAssignmentHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;
use wcf\util\MessageUtil;
use wcf\util\StringUtil;

/**
 * Executes user profile-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User
 */
class UserProfileAction extends UserAction {
	/**
	 * @inheritDoc
	 */
	protected $allowGuestAccess = ['getUserProfile', 'getDetailedActivityPointList'];
	
	/**
	 * user profile object
	 * @var	\wcf\data\user\UserProfile
	 */
	public $userProfile = null;
	
	/**
	 * Validates parameters for signature preview.
	 */
	public function validateGetMessagePreview() {
		$this->readString('message', true, 'data');
		
		if (!isset($this->parameters['options'])) {
			throw new UserInputException('options');
		}
		
		if (isset($this->parameters['options']['enableBBCodes']) && WCF::getSession()->getPermission('user.signature.canUseBBCodes')) {
			$disallowedBBCodes = BBCodeParser::getInstance()->validateBBCodes($this->parameters['data']['message'], explode(',', WCF::getSession()->getPermission('user.signature.allowedBBCodes')));
			if (!empty($disallowedBBCodes)) {
				throw new UserInputException('message', WCF::getLanguage()->getDynamicVariable('wcf.message.error.disallowedBBCodes', [
					'disallowedBBCodes' => $disallowedBBCodes
				]));
			}
		}
	}
	
	/**
	 * Returns a rendered signature preview.
	 * 
	 * @return	array
	 */
	public function getMessagePreview() {
		// get options
		$enableBBCodes = (isset($this->parameters['options']['enableBBCodes'])) ? 1 : 0;
		$enableHtml = (isset($this->parameters['options']['enableHtml'])) ? 1 : 0;
		$enableSmilies = (isset($this->parameters['options']['enableSmilies'])) ? 1 : 0;
		
		// validate permissions for options
		if ($enableBBCodes && !WCF::getSession()->getPermission('user.signature.canUseBBCodes')) $enableBBCodes = 0;
		if ($enableHtml && !WCF::getSession()->getPermission('user.signature.canUseHtml')) $enableHtml = 0;
		if ($enableSmilies && !WCF::getSession()->getPermission('user.signature.canUseSmilies')) $enableSmilies = 0;
		
		// parse message
		$message = StringUtil::trim($this->parameters['data']['message']);
		$preview = MessageParser::getInstance()->parse($message, $enableSmilies, $enableHtml, $enableBBCodes, false);
		
		return [
			'message' => $preview
		];
	}
	
	/**
	 * Validates user profile preview.
	 */
	public function validateGetUserProfile() {
		if (count($this->objectIDs) != 1) {
			throw new UserInputException('objectIDs');
		}
	}
	
	/**
	 * Returns user profile preview.
	 * 
	 * @return	array
	 */
	public function getUserProfile() {
		$userID = reset($this->objectIDs);
		
		if ($userID) {
			$userProfileList = new UserProfileList();
			$userProfileList->getConditionBuilder()->add("user_table.userID = ?", [$userID]);
			$userProfileList->readObjects();
			$userProfiles = $userProfileList->getObjects();
			
			if (empty($userProfiles)) {
				WCF::getTPL()->assign('unknownUser', true);
			}
			else {
				WCF::getTPL()->assign('user', reset($userProfiles));
			}
		}
		else {
			WCF::getTPL()->assign('unknownUser', true);
		}
		
		return [
			'template' => WCF::getTPL()->fetch('userProfilePreview'),
			'userID' => $userID
		];
	}
	
	/**
	 * Validates detailed activity point list
	 */
	public function validateGetDetailedActivityPointList() {
		if (count($this->objectIDs) != 1) {
			throw new UserInputException('objectIDs');
		}
		$this->userProfile = UserProfileRuntimeCache::getInstance()->getObject(reset($this->objectIDs));
		
		if ($this->userProfile === null) {
			throw new UserInputException('objectIDs');
		}
	}
	
	/**
	 * Returns detailed activity point list.
	 * 
	 * @return	array
	 */
	public function getDetailedActivityPointList() {
		$activityPointObjectTypes = [];
		foreach (ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.user.activityPointEvent') as $objectType) {
			$activityPointObjectTypes[$objectType->objectTypeID] = $objectType;
		}
		
		$conditionBuilder = new PreparedStatementConditionBuilder();
		$conditionBuilder->add('userID = ?', [$this->userProfile->userID]);
		$conditionBuilder->add('objectTypeID IN (?)', [array_keys($activityPointObjectTypes)]);
		
		$sql = "SELECT	objectTypeID, activityPoints
			FROM	wcf".WCF_N."_user_activity_point
			".$conditionBuilder;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditionBuilder->getParameters());
		while ($row = $statement->fetchArray()) {
			$activityPointObjectTypes[$row['objectTypeID']]->activityPoints = $row['activityPoints'];
		}
		
		WCF::getTPL()->assign([
			'activityPointObjectTypes' => $activityPointObjectTypes,
			'user' => $this->userProfile
		]);
		
		return [
			'template' => WCF::getTPL()->fetch('detailedActivityPointList'),
			'userID' => $this->userProfile->userID
		];
	}
	
	/**
	 * Validates parameters to begin profile inline editing.
	 */
	public function validateBeginEdit() {
		if (!empty($this->objectIDs) && count($this->objectIDs) == 1) {
			$userID = reset($this->objectIDs);
			$this->userProfile = UserProfileRuntimeCache::getInstance()->getObject($userID);
		}
		
		if ($this->userProfile === null || !$this->userProfile->userID) {
			throw new UserInputException('objectIDs');
		}
		
		if ($this->userProfile->userID != WCF::getUser()->userID) {
			if (!$this->userProfile->canEdit()) {
				throw new PermissionDeniedException();
			}
		}
		else if (!$this->userProfile->canEditOwnProfile()) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * Begins profile inline editing.
	 * 
	 * @return	array
	 */
	public function beginEdit() {
		$optionTree = $this->getOptionHandler($this->userProfile->getDecoratedObject())->getOptionTree();
		WCF::getTPL()->assign([
			'errorType' => [],
			'optionTree' => $optionTree,
			'__userTitle' => $this->userProfile->userTitle
		]);
		
		return [
			'template' => WCF::getTPL()->fetch('userProfileAboutEditable')
		];
	}
	
	/**
	 * Validates parameters to save changes to user profile.
	 */
	public function validateSave() {
		$this->validateBeginEdit();
		
		if (!isset($this->parameters['values']) || !is_array($this->parameters['values'])) {
			$this->parameters['values'] = [];
		}
		
		if (isset($this->parameters['values']['__userTitle']) && !WCF::getSession()->getPermission('user.profile.canEditUserTitle')) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * Saves changes to user profile.
	 * 
	 * @return	array
	 */
	public function save() {
		$userTitle = null;
		if (isset($this->parameters['values']['__userTitle'])) {
			$userTitle = StringUtil::trim(MessageUtil::stripCrap($this->parameters['values']['__userTitle']));
			unset($this->parameters['values']['__userTitle']);
		}
		
		$optionHandler = $this->getOptionHandler($this->userProfile->getDecoratedObject());
		$optionHandler->readUserInput($this->parameters);
		
		$errors = $optionHandler->validate();
		
		// validate user title
		if ($userTitle !== null) {
			try {
				if (mb_strlen($userTitle) > USER_TITLE_MAX_LENGTH) {
					throw new UserInputException('__userTitle', 'tooLong');
				}
				if (!StringUtil::executeWordFilter($userTitle, USER_FORBIDDEN_TITLES)) {
					throw new UserInputException('__userTitle', 'forbidden');
				}
			}
			catch (UserInputException $e) {
				$errors[$e->getField()] = $e->getType();
			}
		}
		
		// validation was successful
		if (empty($errors)) {
			$saveOptions = $optionHandler->save();
			$data = [
				'options' => $saveOptions
			];
			
			// save user title
			if ($userTitle !== null) {
				$data['data'] = [
					'userTitle' => $userTitle
				];
			}
			
			$userAction = new UserAction([$this->userProfile->userID], 'update', $data);
			$userAction->executeAction();
			
			// check if the user will be automatically added to new
			// user groups because of the changed user options
			UserGroupAssignmentHandler::getInstance()->checkUsers([$this->userProfile->userID]);
			
			// return parsed template
			$user = new User($this->userProfile->userID);
			
			// reload option handler
			$optionHandler = $this->getOptionHandler($user, false);
			
			$options = $optionHandler->getOptionTree();
			WCF::getTPL()->assign([
				'options' => $options,
				'userID' => $this->userProfile->userID
			]);
			
			return [
				'success' => true,
				'template' => WCF::getTPL()->fetch('userProfileAbout')
			];
		}
		else {
			// validation failed
			WCF::getTPL()->assign([
				'errorType' => $errors,
				'optionTree' => $optionHandler->getOptionTree(),
				'__userTitle' => ($userTitle !== null ? $userTitle : $this->userProfile->userTitle)
			]);
			
			return [
				'success' => false,
				'template' => WCF::getTPL()->fetch('userProfileAboutEditable')
			];
		}
	}
	
	/**
	 * Updates user ranks.
	 */
	public function updateUserRank() {
		if (empty($this->objects)) {
			$this->readObjects();
		}
		
		$resetUserIDs = [];
		foreach ($this->getObjects() as $user) {
			$conditionBuilder = new PreparedStatementConditionBuilder();
			$conditionBuilder->add('user_rank.groupID IN (?)', [$user->getGroupIDs()]);
			$conditionBuilder->add('user_rank.requiredPoints <= ?', [$user->activityPoints]);
			if ($user->gender) $conditionBuilder->add('user_rank.requiredGender IN (?)', [[0, $user->gender]]);
			else $conditionBuilder->add('user_rank.requiredGender = ?', [0]);
			
			$sql = "SELECT		user_rank.rankID
				FROM		wcf".WCF_N."_user_rank user_rank
				LEFT JOIN	wcf".WCF_N."_user_group user_group
				ON		(user_group.groupID = user_rank.groupID)
				".$conditionBuilder."
				ORDER BY	user_group.priority DESC, user_rank.requiredPoints DESC, user_rank.requiredGender DESC";
			$statement = WCF::getDB()->prepareStatement($sql, 1);
			$statement->execute($conditionBuilder->getParameters());
			$row = $statement->fetchArray();
			if ($row === false) {
				if ($user->rankID) {
					$user->update(['rankID' => null]);
					$resetUserIDs[] = $user->userID;
				}
			}
			else {
				if ($row['rankID'] != $user->rankID) {
					$user->update(['rankID' => $row['rankID']]);
					$resetUserIDs[] = $user->userID;
				}
			}
		}
		
		if (!empty($resetUserIDs)) {
			UserStorageHandler::getInstance()->reset($resetUserIDs, 'userRank');
		}
	}
	
	/**
	 * Updates user online markings.
	 */
	public function updateUserOnlineMarking() {
		if (empty($this->objects)) {
			$this->readObjects();
		}
		
		$userToGroup = [];
		foreach ($this->getObjects() as $user) {
			$conditionBuilder = new PreparedStatementConditionBuilder();
			$conditionBuilder->add('groupID IN (?)', [$user->getGroupIDs()]);
			
			$sql = "SELECT		groupID
				FROM		wcf".WCF_N."_user_group
				".$conditionBuilder."
				ORDER BY	priority DESC";
			$statement = WCF::getDB()->prepareStatement($sql, 1);
			$statement->execute($conditionBuilder->getParameters());
			$row = $statement->fetchArray();
			if ($row['groupID'] != $user->userOnlineGroupID) {
				$userToGroup[$user->userID] = $row['groupID'];
			}
		}
		
		if (!empty($userToGroup)) {
			$sql = "UPDATE	wcf".WCF_N."_user
				SET	userOnlineGroupID = ?
				WHERE	userID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			WCF::getDB()->beginTransaction();
			foreach ($userToGroup as $userID => $groupID) {
				$statement->execute([
					$groupID,
					$userID
				]);
			}
			WCF::getDB()->commitTransaction();
		}
	}
	
	/**
	 * Returns the user option handler object.
	 * 
	 * @param	\wcf\data\user\User	$user
	 * @param	boolean			$editMode
	 * @return	\wcf\system\option\user\UserOptionHandler
	 */
	protected function getOptionHandler(User $user, $editMode = true) {
		$optionHandler = new UserOptionHandler(false, '', 'profile');
		if (!$editMode) {
			$optionHandler->showEmptyOptions(false);
			$optionHandler->enableEditMode(false);
		}
		$optionHandler->setUser($user);
		
		return $optionHandler;
	}
}
