<?php
namespace wcf\data\user;
use wcf\data\user\avatar\UserAvatarAction;
use wcf\data\user\group\UserGroup;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IClipboardAction;
use wcf\data\ISearchAction;
use wcf\system\clipboard\ClipboardHandler;
use wcf\system\comment\CommentHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\event\EventHandler;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\mail\Mail;
use wcf\system\request\RequestHandler;
use wcf\system\WCF;
use wcf\util\UserRegistrationUtil;

/**
 * Executes user-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User
 * 
 * @method	UserEditor[]	getObjects()
 * @method	UserEditor	getSingleObject()
 */
class UserAction extends AbstractDatabaseObjectAction implements IClipboardAction, ISearchAction {
	/**
	 * @inheritDoc
	 */
	public $className = UserEditor::class;
	
	/**
	 * @inheritDoc
	 */
	protected $allowGuestAccess = ['getSearchResultList'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsCreate = ['admin.user.canAddUser'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsDelete = ['admin.user.canDeleteUser'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsUpdate = ['admin.user.canEditUser'];
	
	/**
	 * @inheritDoc
	 */
	protected $requireACP = ['create', 'delete', 'disable', 'enable'];
	
	/**
	 * Validates permissions and parameters.
	 */
	public function validateCreate() {
		$this->readString('password', false, 'data');
	}
	
	/**
	 * Validates accessible groups.
	 * 
	 * @param	boolean		$ignoreOwnUser
	 * @throws	PermissionDeniedException
	 * @throws	UserInputException
	 */
	protected function __validateAccessibleGroups($ignoreOwnUser = true) {
		if ($ignoreOwnUser) {
			if (in_array(WCF::getUser()->userID, $this->objectIDs)) {
				unset($this->objectIDs[array_search(WCF::getUser()->userID, $this->objectIDs)]);
				if (isset($this->objects[WCF::getUser()->userID])) {
					unset($this->objects[WCF::getUser()->userID]);
				}
			}
		}
		
		// list might be empty because only our own user id was given
		if (empty($this->objectIDs)) {
			throw new UserInputException('objectIDs');
		}
		
		// validate groups
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("userID IN (?)", [$this->objectIDs]);
		
		$sql = "SELECT	DISTINCT groupID
			FROM	wcf".WCF_N."_user_to_group
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		$groupIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);
		
		if (!UserGroup::isAccessibleGroup($groupIDs)) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * Validates permissions and parameters.
	 */
	public function validateDelete() {
		// read and validate user objects
		parent::validateDelete();
		
		$this->__validateAccessibleGroups();
	}
	
	/**
	 * @inheritDoc
	 */
	public function delete() {
		if (empty($this->objects)) {
			$this->readObjects();
		}
		
		// delete avatars
		$avatarIDs = [];
		foreach ($this->getObjects() as $user) {
			if ($user->avatarID) $avatarIDs[] = $user->avatarID;
		}
		if (!empty($avatarIDs)) {
			$action = new UserAvatarAction($avatarIDs, 'delete');
			$action->executeAction();
		}
		
		// delete profile comments
		if (!empty($this->objectIDs)) {
			CommentHandler::getInstance()->deleteObjects('com.woltlab.wcf.user.profileComment', $this->objectIDs);
		}
		
		$returnValue = parent::delete();
		
		return $returnValue;
	}
	
	/**
	 * Validates permissions and parameters.
	 */
	public function validateUpdate() {
		// read objects
		if (empty($this->objects)) {
			$this->readObjects();
			
			if (empty($this->objects)) {
				throw new UserInputException('objectIDs');
			}
		}
		
		// disallow updating of anything except for options outside of ACP
		if (RequestHandler::getInstance()->isACPRequest() && (count($this->parameters) != 1 || !isset($this->parameters['options']))) {
			throw new PermissionDeniedException();
		}
		
		try {
			WCF::getSession()->checkPermissions($this->permissionsUpdate);
		}
		catch (PermissionDeniedException $e) {
			// check if we're editing ourselves
			if (count($this->objects) == 1 && ($this->objects[0]->userID == WCF::getUser()->userID)) {
				$count = count($this->parameters);
				if ($count > 1 || ($count == 1 && !isset($this->parameters['options']))) {
					throw new PermissionDeniedException();
				}
			}
			
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * Validates the ban action.
	 */
	public function validateBan() {
		$this->validateUnban();
		
		$this->readString('banReason', true);
		$this->readString('banExpires', true);
	}
	
	/**
	 * Validates the unban action.
	 */
	public function validateUnban() {
		WCF::getSession()->checkPermissions(['admin.user.canBanUser']);
		
		$this->__validateAccessibleGroups();
	}
	
	/**
	 * Bans users.
	 */
	public function ban() {
		$banExpires = $this->parameters['banExpires'];
		if ($banExpires) {
			$banExpires = strtotime($banExpires);
		}
		else {
			$banExpires = 0;
		}
		
		$conditionBuilder = new PreparedStatementConditionBuilder();
		$conditionBuilder->add('userID IN (?)', [$this->objectIDs]);
		
		$sql = "UPDATE	wcf".WCF_N."_user
			SET	banned = ?,
				banReason = ?,
				banExpires = ?
			".$conditionBuilder;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(
			array_merge([
				1,
				$this->parameters['banReason'],
				$banExpires
			], $conditionBuilder->getParameters())
		);
		
		$this->unmarkItems();
	}
	
	/**
	 * Unbans users.
	 */
	public function unban() {
		$conditionBuilder = new PreparedStatementConditionBuilder();
		$conditionBuilder->add('userID IN (?)', [$this->objectIDs]);
		
		$sql = "UPDATE	wcf".WCF_N."_user
			SET	banned = ?,
				banExpires = ?
			".$conditionBuilder;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(
			array_merge([
				0,
				0
			], $conditionBuilder->getParameters())
		);
	}
	
	/**
	 * @inheritDoc
	 * @return	User
	 */
	public function create() {
		/** @var User $user */
		$user = parent::create();
		$userEditor = new UserEditor($user);
		
		// updates user options
		if (isset($this->parameters['options'])) {
			$userEditor->updateUserOptions($this->parameters['options']);
		}
		
		// insert user groups
		$addDefaultGroups = (isset($this->parameters['addDefaultGroups'])) ? $this->parameters['addDefaultGroups'] : true;
		$groupIDs = (isset($this->parameters['groups'])) ? $this->parameters['groups'] : [];
		$userEditor->addToGroups($groupIDs, false, $addDefaultGroups);
		
		// insert visible languages
		if (!isset($this->parameters['languageIDs'])) {
			// using the 'languages' key is deprecated since WCF 2.1, please use 'languageIDs' instead
			$this->parameters['languageIDs'] = (!empty($this->parameters['languages'])) ? $this->parameters['languages'] : [];
		}
		$userEditor->addToLanguages($this->parameters['languageIDs'], false);
		
		if (PACKAGE_ID) {
			// set default notifications
			$sql = "INSERT INTO	wcf".WCF_N."_user_notification_event_to_user
						(userID, eventID, mailNotificationType)
				SELECT		?, eventID, presetMailNotificationType
				FROM		wcf".WCF_N."_user_notification_event
				WHERE		preset = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$user->userID, 1]);
			
			// update user rank
			if (MODULE_USER_RANK) {
				$action = new UserProfileAction([$userEditor], 'updateUserRank');
				$action->executeAction();
			}
			// update user online marking
			$action = new UserProfileAction([$userEditor], 'updateUserOnlineMarking');
			$action->executeAction();
		}
		
		return $user;
	}
	
	/**
	 * @inheritDoc
	 */
	public function update() {
		if (isset($this->parameters['data']) || isset($this->parameters['counters'])) { 
			parent::update();
			
			if (isset($this->parameters['data']['languageID'])) {
				foreach ($this->getObjects() as $object) {
					if ($object->userID == WCF::getUser()->userID) {
						if ($this->parameters['data']['languageID'] != WCF::getUser()->languageID) {
							WCF::setLanguage($this->parameters['data']['languageID']);
						}
						
						break;
					}
				}
			}
		}
		else {
			if (empty($this->objects)) {
				$this->readObjects();
			}
		}
		
		$groupIDs = (isset($this->parameters['groups'])) ? $this->parameters['groups'] : [];
		$languageIDs = (isset($this->parameters['languageIDs'])) ? $this->parameters['languageIDs'] : [];
		$removeGroups = (isset($this->parameters['removeGroups'])) ? $this->parameters['removeGroups'] : [];
		$userOptions = (isset($this->parameters['options'])) ? $this->parameters['options'] : [];
		
		if (!empty($groupIDs)) {
			$action = new UserAction($this->objects, 'addToGroups', [
				'groups' => $groupIDs,
				'addDefaultGroups' => false
			]);
			$action->executeAction();
		}
		
		if (!empty($removeGroups)) {
			$action = new UserAction($this->objects, 'removeFromGroups', [
				'groups' => $removeGroups
			]);
			$action->executeAction();
		}
		
		foreach ($this->getObjects() as $userEditor) {
			if (!empty($userOptions)) {
				$userEditor->updateUserOptions($userOptions);
			}
			
			if (!empty($languageIDs)) {
				$userEditor->addToLanguages($languageIDs);
			}
		}
		
		// handle user rename
		if (count($this->objects) == 1 && !empty($this->parameters['data']['username'])) {
			if ($this->objects[0]->username != $this->parameters['data']['username']) {
				$userID = $this->objects[0]->userID;
				$username = $this->parameters['data']['username'];
				
				WCF::getDB()->beginTransaction();
				
				// update comments
				$sql = "UPDATE	wcf".WCF_N."_comment
					SET	username = ?
					WHERE	userID = ?";
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute([$username, $userID]);
				
				// update comment responses
				$sql = "UPDATE	wcf".WCF_N."_comment_response
					SET	username = ?
					WHERE	userID = ?";
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute([$username, $userID]);
				
				// update media
				$sql = "UPDATE	wcf".WCF_N."_media
					SET	username = ?
					WHERE	userID = ?";
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute([$username, $userID]);
				
				// update modification log
				$sql = "UPDATE	wcf".WCF_N."_modification_log
					SET	username = ?
					WHERE	userID = ?";
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute([$username, $userID]);
				
				WCF::getDB()->commitTransaction();
				
				// fire event to handle other database tables
				EventHandler::getInstance()->fireAction($this, 'rename');
			}
		}
	}
	
	/**
	 * Remove users from given groups.
	 */
	public function removeFromGroups() {
		if (empty($this->objects)) {
			$this->readObjects();
		}
		
		$groupIDs = $this->parameters['groups'];
		
		foreach ($this->getObjects() as $userEditor) {
			$userEditor->removeFromGroups($groupIDs);
		}
		
		//reread objects
		$this->objects = [];
		UserEditor::resetCache();
		$this->readObjects();
		
		if (MODULE_USER_RANK) {
			$action = new UserProfileAction($this->objects, 'updateUserRank');
			$action->executeAction();
		}
		if (MODULE_USERS_ONLINE) {
			$action = new UserProfileAction($this->objects, 'updateUserOnlineMarking');
			$action->executeAction();
		}
	}
	
	/**
	 * Add users to given groups.
	 */
	public function addToGroups() {
		if (empty($this->objects)) {
			$this->readObjects();
		}
		
		$groupIDs = $this->parameters['groups'];
		$deleteOldGroups = $addDefaultGroups = true;
		if (isset($this->parameters['deleteOldGroups'])) $deleteOldGroups = $this->parameters['deleteOldGroups'];
		if (isset($this->parameters['addDefaultGroups'])) $addDefaultGroups = $this->parameters['addDefaultGroups'];
		
		foreach ($this->getObjects() as $userEditor) {
			$userEditor->addToGroups($groupIDs, $deleteOldGroups, $addDefaultGroups);
		}
		
		//reread objects
		$this->objects = [];
		UserEditor::resetCache();
		$this->readObjects();
		
		if (MODULE_USER_RANK) {
			$action = new UserProfileAction($this->objects, 'updateUserRank');
			$action->executeAction();
		}
		if (MODULE_USERS_ONLINE) {
			$action = new UserProfileAction($this->objects, 'updateUserOnlineMarking');
			$action->executeAction();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function validateGetSearchResultList() {
		$this->readBoolean('includeUserGroups', false, 'data');
		$this->readString('searchString', false, 'data');
		
		if (isset($this->parameters['data']['excludedSearchValues']) && !is_array($this->parameters['data']['excludedSearchValues'])) {
			throw new UserInputException('excludedSearchValues');
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function getSearchResultList() {
		$searchString = $this->parameters['data']['searchString'];
		$excludedSearchValues = [];
		if (isset($this->parameters['data']['excludedSearchValues'])) {
			$excludedSearchValues = $this->parameters['data']['excludedSearchValues'];
		}
		$list = [];
		
		if ($this->parameters['data']['includeUserGroups']) {
			$accessibleGroups = UserGroup::getAccessibleGroups();
			foreach ($accessibleGroups as $group) {
				$groupName = $group->getName();
				if (!in_array($groupName, $excludedSearchValues)) {
					$pos = mb_strripos($groupName, $searchString);
					if ($pos !== false && $pos == 0) {
						$list[] = [
							'label' => $groupName,
							'objectID' => $group->groupID,
							'type' => 'group'
						];
					}
				}
			}
		}
		
		// find users
		$userProfileList = new UserProfileList();
		$userProfileList->getConditionBuilder()->add("username LIKE ?", [$searchString.'%']);
		if (!empty($excludedSearchValues)) {
			$userProfileList->getConditionBuilder()->add("username NOT IN (?)", [$excludedSearchValues]);
		}
		$userProfileList->sqlLimit = 10;
		$userProfileList->readObjects();
		
		foreach ($userProfileList as $userProfile) {
			$list[] = [
				'icon' => $userProfile->getAvatar()->getImageTag(16),
				'label' => $userProfile->username,
				'objectID' => $userProfile->userID,
				'type' => 'user'
			];
		}
		
		return $list;
	}
	
	/**
	 * @inheritDoc
	 */
	public function validateUnmarkAll() {
		// does nothing
	}
	
	/**
	 * @inheritDoc
	 */
	public function unmarkAll() {
		ClipboardHandler::getInstance()->removeItems(ClipboardHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.user'));
	}
	
	/**
	 * Unmarks users.
	 * 
	 * @param	integer[]	$userIDs
	 */
	protected function unmarkItems(array $userIDs = []) {
		if (empty($userIDs)) {
			$userIDs = $this->objectIDs;
		}
		
		if (!empty($userIDs)) {
			ClipboardHandler::getInstance()->unmark($userIDs, ClipboardHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.user'));
		}
	}
	
	/**
	 * Validates the enable action.
	 */
	public function validateEnable() {
		WCF::getSession()->checkPermissions(['admin.user.canEnableUser']);
		
		$this->__validateAccessibleGroups();
	}
	
	/**
	 * Validates the disable action.
	 */
	public function validateDisable() {
		$this->validateEnable();
	}
	
	/**
	 * Enables users.
	 */
	public function enable() {
		if (empty($this->objects)) $this->readObjects();
		
		$action = new UserAction($this->objects, 'update', [
			'data' => [
				'activationCode' => 0
			],
			'removeGroups' => UserGroup::getGroupIDsByType([UserGroup::GUESTS])
		]);
		$action->executeAction();
		$action = new UserAction($this->objects, 'addToGroups', [
			'groups' => UserGroup::getGroupIDsByType([UserGroup::USERS]),
			'deleteOldGroups' => false,
			'addDefaultGroups' => false
		]);
		$action->executeAction();
		
		// send e-mail notification
		if (empty($this->parameters['skipNotification'])) {
			foreach ($this->getObjects() as $user) {
				$mail = new Mail([$user->username => $user->email], $user->getLanguage()->getDynamicVariable('wcf.acp.user.activation.mail.subject'), $user->getLanguage()->getDynamicVariable('wcf.acp.user.activation.mail', [
					'username' => $user->username
				]));
				$mail->send();
			}
		}
		
		$this->unmarkItems();
	}
	
	/**
	 * Disables users.
	 */
	public function disable() {
		if (empty($this->objects)) $this->readObjects();
		
		$action = new UserAction($this->objects, 'update', [
			'data' => [
				'activationCode' => UserRegistrationUtil::getActivationCode()
			],
			'removeGroups' => UserGroup::getGroupIDsByType([UserGroup::USERS])
		]);
		$action->executeAction();
		$action = new UserAction($this->objects, 'addToGroups', [
			'groups' => UserGroup::getGroupIDsByType([UserGroup::GUESTS]),
			'deleteOldGroups' => false,
			'addDefaultGroups' => false
		]);
		$action->executeAction();
		
		$this->unmarkItems();
	}
	
	/**
	 * @inheritDoc
	 */
	protected function readObjects() {
		if (empty($this->objectIDs)) {
			return;
		}
		
		// get base class
		$baseClass = call_user_func([$this->className, 'getBaseClass']);
		
		// get objects
		$sql = "SELECT		user_option_value.*, user_table.*
			FROM		wcf".WCF_N."_user user_table
			LEFT JOIN	wcf".WCF_N."_user_option_value user_option_value
			ON		(user_option_value.userID = user_table.userID)
			WHERE		user_table.userID IN (".str_repeat('?,', count($this->objectIDs) - 1)."?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($this->objectIDs);
		while ($object = $statement->fetchObject($baseClass)) {
			$this->objects[] = new $this->className($object);
		}
	}
	
	/**
	 * Validates the 'disableSignature' action.
	 */
	public function validateDisableSignature() {
		$this->validateEnableSignature();
		
		$this->readString('disableSignatureReason', true);
		$this->readString('disableSignatureExpires', true);
	}
	
	/**
	 * Disables the signature of the handled users.
	 */
	public function disableSignature() {
		if (empty($this->objects)) {
			$this->readObjects();
		}
		
		$disableSignatureExpires = $this->parameters['disableSignatureExpires'];
		if ($disableSignatureExpires) {
			$disableSignatureExpires = strtotime($disableSignatureExpires);
		}
		else {
			$disableSignatureExpires = 0;
		}
		
		foreach ($this->getObjects() as $userEditor) {
			$userEditor->update([
				'disableSignature' => 1,
				'disableSignatureReason' => $this->parameters['disableSignatureReason'],
				'disableSignatureExpires' => $disableSignatureExpires
			]);
		}
	}
	
	/**
	 * Validates the 'enableSignature' action.
	 */
	public function validateEnableSignature() {
		WCF::getSession()->checkPermissions(['admin.user.canDisableSignature']);
		
		$this->__validateAccessibleGroups();
		
		if (empty($this->objects)) {
			$this->readObjects();
			
			if (empty($this->objects)) {
				throw new UserInputException('objectIDs');
			}
		}
	}
	
	/**
	 * Enables the signature of the handled users.
	 */
	public function enableSignature() {
		if (empty($this->objects)) {
			$this->readObjects();
		}
		
		foreach ($this->getObjects() as $userEditor) {
			$userEditor->update([
				'disableSignature' => 0
			]);
		}
	}
	
	/**
	 * Validates the 'disableAvatar' action.
	 */
	public function validateDisableAvatar() {
		$this->validateEnableAvatar();
		
		$this->readString('disableAvatarReason', true);
		$this->readString('disableAvatarExpires', true);
	}
	
	/**
	 * Disables the avatar of the handled users.
	 */
	public function disableAvatar() {
		if (empty($this->objects)) {
			$this->readObjects();
		}
		
		$disableAvatarExpires = $this->parameters['disableAvatarExpires'];
		if ($disableAvatarExpires) {
			$disableAvatarExpires = strtotime($disableAvatarExpires);
		}
		else {
			$disableAvatarExpires = 0;
		}
		
		foreach ($this->getObjects() as $userEditor) {
			$userEditor->update([
				'disableAvatar' => 1,
				'disableAvatarReason' => $this->parameters['disableAvatarReason'],
				'disableAvatarExpires' => $disableAvatarExpires
			]);
		}
	}
	
	/**
	 * Validates the 'enableAvatar' action.
	 */
	public function validateEnableAvatar() {
		WCF::getSession()->checkPermissions(['admin.user.canDisableAvatar']);
		
		$this->__validateAccessibleGroups();
		
		if (empty($this->objects)) {
			$this->readObjects();
			
			if (empty($this->objects)) {
				throw new UserInputException('objectIDs');
			}
		}
	}
	
	/**
	 * Enables the avatar of the handled users.
	 */
	public function enableAvatar() {
		if (empty($this->objects)) {
			$this->readObjects();
		}
		
		foreach ($this->getObjects() as $userEditor) {
			$userEditor->update([
				'disableAvatar' => 0
			]);
		}
	}
	
	/**
	 * Validates parameters to retrieve the social network privacy settings.
	 * @deprecated 3.0
	 */
	public function validateGetSocialNetworkPrivacySettings() {
		// does nothing
	}
	
	/**
	 * Returns the social network privacy settings.
	 * @deprecated 3.0
	 */
	public function getSocialNetworkPrivacySettings() {
		// does nothing
	}
	
	/**
	 * Validates the 'saveSocialNetworkPrivacySettings' action.
	 * @deprecated 3.0
	 */
	public function validateSaveSocialNetworkPrivacySettings() {
		// does nothing
	}
	
	/**
	 * Saves the social network privacy settings.
	 * @deprecated 3.0
	 */
	public function saveSocialNetworkPrivacySettings() {
		// does nothing
	}
}
