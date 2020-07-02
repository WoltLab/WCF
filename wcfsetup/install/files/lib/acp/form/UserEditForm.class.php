<?php
namespace wcf\acp\form;
use wcf\data\style\Style;
use wcf\data\user\avatar\Gravatar;
use wcf\data\user\avatar\UserAvatar;
use wcf\data\user\avatar\UserAvatarAction;
use wcf\data\user\cover\photo\UserCoverPhoto;
use wcf\data\user\group\UserGroup;
use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\data\user\UserEditor;
use wcf\data\user\UserProfileAction;
use wcf\form\AbstractForm;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\moderation\queue\ModerationQueueManager;
use wcf\system\style\StyleHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the user edit form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 */
class UserEditForm extends UserAddForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.user.list';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.user.canEditUser'];
	
	/**
	 * user id
	 * @var	integer
	 */
	public $userID = 0;
	
	/**
	 * user editor object
	 * @var	UserEditor
	 */
	public $user;
	
	/**
	 * ban status
	 * @var	boolean
	 */
	public $banned = 0;
	
	/**
	 * ban reason
	 * @var	string
	 */
	public $banReason = '';
	
	/**
	 * date when the ban expires
	 * @var	integer
	 */
	public $banExpires = 0;
	
	/**
	 * user avatar object
	 * @var	UserAvatar
	 */
	public $userAvatar;
	
	/**
	 * avatar type
	 * @var	string
	 */
	public $avatarType = 'none';
	
	/**
	 * true to disable this avatar
	 * @var	boolean
	 */
	public $disableAvatar = 0;
	
	/**
	 * reason
	 * @var	string
	 */
	public $disableAvatarReason = '';
	
	/**
	 * date when the avatar will be enabled again
	 * @var	integer
	 */
	public $disableAvatarExpires = 0;
	
	/**
	 * user cover photo object
	 * @var UserCoverPhoto
	 */
	public $userCoverPhoto;
	
	/**
	 * true to disable this cover photo
	 * @var	boolean
	 */
	public $disableCoverPhoto = 0;
	
	/**
	 * reason
	 * @var	string
	 */
	public $disableCoverPhotoReason = '';
	
	/**
	 * date when the cover photo will be enabled again
	 * @var	integer
	 */
	public $disableCoverPhotoExpires = 0;
	
	/**
	 * true to delete the current cover photo
	 * @var boolean
	 */
	public $deleteCoverPhoto = 0;
	
	/**
	 * true to delete the current auth data
	 * @var	boolean
	 */
	public $disconnect3rdParty = 0;
	
	/**
	 * list of available styles for the edited user
	 * @var         Style[]
	 * @since       5.3
	 */
	public $availableStyles = [];
	
	/**
	 * id of the used style
	 * @var         int
	 * @since       5.3
	 */
	public $styleID = 0;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		if (isset($_REQUEST['id'])) $this->userID = intval($_REQUEST['id']);
		$user = new User($this->userID);
		if (!$user->userID) {
			throw new IllegalLinkException();
		}
		
		$this->user = new UserEditor($user);
		if (!UserGroup::isAccessibleGroup($this->user->getGroupIDs())) {
			throw new PermissionDeniedException();
		}
		
		parent::readParameters();
	}
	
	/**
	 * @inheritDoc
	 */
	protected function initOptionHandler() {
		/** @noinspection PhpUndefinedMethodInspection */
		$this->optionHandler->setUser($this->user->getDecoratedObject());
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (!WCF::getSession()->getPermission('admin.user.canEditPassword') || !empty($this->user->authData)) $this->password = $this->confirmPassword = '';
		if (!WCF::getSession()->getPermission('admin.user.canEditMailAddress')) $this->email = $this->confirmEmail = $this->user->email;
		
		if (!empty($_POST['banned'])) $this->banned = 1;
		if (isset($_POST['banReason'])) $this->banReason = StringUtil::trim($_POST['banReason']);
		if ($this->banned && !isset($_POST['banNeverExpires'])) {
			if (isset($_POST['banExpires'])) $this->banExpires = @strtotime(StringUtil::trim($_POST['banExpires']));
		}
		
		if (isset($_POST['avatarType'])) $this->avatarType = $_POST['avatarType'];
		if (isset($_POST['styleID'])) $this->styleID = intval($_POST['styleID']);
		
		if (WCF::getSession()->getPermission('admin.user.canDisableAvatar')) {
			if (!empty($_POST['disableAvatar'])) $this->disableAvatar = 1;
			if (isset($_POST['disableAvatarReason'])) $this->disableAvatarReason = StringUtil::trim($_POST['disableAvatarReason']);
			if ($this->disableAvatar && !isset($_POST['disableAvatarNeverExpires'])) {
				if (isset($_POST['disableAvatarExpires'])) $this->disableAvatarExpires = @strtotime(StringUtil::trim($_POST['disableAvatarExpires']));
			}
		}
		
		if (WCF::getSession()->getPermission('admin.user.canDisableCoverPhoto')) {
			if (isset($_POST['deleteCoverPhoto'])) $this->deleteCoverPhoto = 1;
			if (!empty($_POST['disableCoverPhoto'])) $this->disableCoverPhoto = 1;
			if (isset($_POST['disableCoverPhotoReason'])) $this->disableCoverPhotoReason = StringUtil::trim($_POST['disableCoverPhotoReason']);
			if ($this->disableCoverPhoto && !isset($_POST['disableCoverPhotoNeverExpires'])) {
				if (isset($_POST['disableCoverPhotoExpires'])) $this->disableCoverPhotoExpires = @strtotime(StringUtil::trim($_POST['disableCoverPhotoExpires']));
			}
		}
		
		if (WCF::getSession()->getPermission('admin.user.canEditPassword') && isset($_POST['disconnect3rdParty'])) $this->disconnect3rdParty = 1;
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		if (empty($_POST)) {
			// get visible languages
			$this->readVisibleLanguages();
			
			// default values
			$this->readDefaultValues();
		}
		
		$userProfile = UserProfileRuntimeCache::getInstance()->getObject($this->userID);
		foreach (StyleHandler::getInstance()->getStyles() as $style) {
			if (!$style->isDisabled || $userProfile->getPermission('admin.style.canUseDisabledStyle')) {
				$this->availableStyles[$style->styleID] = $style;
			}
		}
		
		parent::readData();
		
		// get the avatar object
		if ($this->avatarType == 'custom') {
			$this->userAvatar = new UserAvatar($this->user->avatarID);
		}
		
		// get the user cover photo object
		if ($this->user->coverPhotoHash) {
			// If the editing user lacks the permissions to view the cover photo, the system
			// will try to load the default cover photo. However, the default cover photo depends
			// on the style, eventually triggering a change to the template group which will
			// fail in the admin panel.
			if ($userProfile->canSeeCoverPhoto()) {
				$this->userCoverPhoto = UserProfileRuntimeCache::getInstance()->getObject($this->userID)->getCoverPhoto(true);
			}
		}
	}
	
	/**
	 * Sets the selected languages.
	 */
	protected function readVisibleLanguages() {
		$this->visibleLanguages = $this->user->getLanguageIDs();
	}
	
	/**
	 * Sets the default values.
	 */
	protected function readDefaultValues() {
		$this->username = $this->user->username;
		$this->email = $this->confirmEmail = $this->user->email;
		$this->groupIDs = $this->user->getGroupIDs(true);
		$this->languageID = $this->user->languageID;
		$this->banned = $this->user->banned;
		$this->banReason = $this->user->banReason;
		$this->banExpires = $this->user->banExpires;
		$this->userTitle = $this->user->userTitle;
		$this->styleID = $this->user->styleID;
		
		$this->signature = $this->user->signature;
		$this->disableSignature = $this->user->disableSignature;
		$this->disableSignatureReason = $this->user->disableSignatureReason;
		$this->disableSignatureExpires = $this->user->disableSignatureExpires;
		
		$this->disableAvatar = $this->user->disableAvatar;
		$this->disableAvatarReason = $this->user->disableAvatarReason;
		$this->disableAvatarExpires = $this->user->disableAvatarExpires;
		
		$this->disableCoverPhoto = $this->user->disableCoverPhoto;
		$this->disableCoverPhotoReason = $this->user->disableCoverPhotoReason;
		$this->disableCoverPhotoExpires = $this->user->disableCoverPhotoExpires;
		
		if ($this->user->avatarID) $this->avatarType = 'custom';
		else if (MODULE_GRAVATAR && $this->user->enableGravatar) $this->avatarType = 'gravatar';
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'userID' => $this->user->userID,
			'action' => 'edit',
			'url' => '',
			'markedUsers' => 0,
			'user' => $this->user,
			'banned' => $this->banned,
			'banReason' => $this->banReason,
			'avatarType' => $this->avatarType,
			'disableAvatar' => $this->disableAvatar,
			'disableAvatarReason' => $this->disableAvatarReason,
			'disableAvatarExpires' => $this->disableAvatarExpires,
			'userAvatar' => $this->userAvatar,
			'banExpires' => $this->banExpires,
			'userCoverPhoto' => $this->userCoverPhoto,
			'disableCoverPhoto' => $this->disableCoverPhoto,
			'disableCoverPhotoReason' => $this->disableCoverPhotoReason,
			'disableCoverPhotoExpires' => $this->disableCoverPhotoExpires,
			'deleteCoverPhoto' => $this->deleteCoverPhoto,
			'ownerGroupID' => UserGroup::getOwnerGroupID(),
			'availableStyles' => $this->availableStyles,
			'styleID' => $this->styleID,
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		AbstractForm::save();
		
		// handle avatar
		if ($this->avatarType != 'custom') {
			// delete custom avatar
			if ($this->user->avatarID) {
				$action = new UserAvatarAction([$this->user->avatarID], 'delete');
				$action->executeAction();
			}
		}
		
		$avatarData = [];
		switch ($this->avatarType) {
			case 'none':
				$avatarData = [
					'avatarID' => null,
					'enableGravatar' => 0
				];
			break;
			
			case 'custom':
				$avatarData = [
					'enableGravatar' => 0
				];
			break;
			
			case 'gravatar':
				$avatarData = [
					'avatarID' => null,
					'enableGravatar' => 1
				];
			break;
		}
		
		$this->additionalFields = array_merge($this->additionalFields, $avatarData);
		
		if ($this->disconnect3rdParty) {
			$this->additionalFields['authData'] = '';
		}
		
		// add default groups
		$defaultGroups = UserGroup::getAccessibleGroups([UserGroup::GUESTS, UserGroup::EVERYONE, UserGroup::USERS]);
		$oldGroupIDs = $this->user->getGroupIDs(true);
		foreach ($oldGroupIDs as $oldGroupID) {
			if (isset($defaultGroups[$oldGroupID])) {
				$this->groupIDs[] = $oldGroupID;
			}
		}
		$this->groupIDs = array_unique($this->groupIDs);
		
		// save user
		$saveOptions = $this->optionHandler->save();
		
		$data = [
			'data' => array_merge($this->additionalFields, [
				'username' => $this->username,
				'email' => $this->email,
				'password' => $this->password,
				'languageID' => $this->languageID,
				'userTitle' => $this->userTitle,
				'signature' => $this->htmlInputProcessor->getHtml(),
				'styleID' => $this->styleID,
			]),
			'groups' => $this->groupIDs,
			'languageIDs' => $this->visibleLanguages,
			'options' => $saveOptions,
		];
		// handle changed username
		if (mb_strtolower($this->username) != mb_strtolower($this->user->username)) {
			$data['data']['lastUsernameChange'] = TIME_NOW;
			$data['data']['oldUsername'] = $this->user->username;
		}
		
		// handle ban
		if (WCF::getSession()->getPermission('admin.user.canBanUser')) {
			$data['data']['banned'] = $this->banned;
			$data['data']['banReason'] = $this->banReason;
			$data['data']['banExpires'] = $this->banExpires;
		}
		
		// handle disabled signature
		if (WCF::getSession()->getPermission('admin.user.canDisableSignature')) {
			$data['data']['disableSignature'] = $this->disableSignature;
			$data['data']['disableSignatureReason'] = $this->disableSignatureReason;
			$data['data']['disableSignatureExpires'] = $this->disableSignatureExpires;
		}
		
		// handle disabled avatar
		if (WCF::getSession()->getPermission('admin.user.canDisableAvatar')) {
			$data['data']['disableAvatar'] = $this->disableAvatar;
			$data['data']['disableAvatarReason'] = $this->disableAvatarReason;
			$data['data']['disableAvatarExpires'] = $this->disableAvatarExpires;
		}
		
		// handle disabled cover photo
		if (WCF::getSession()->getPermission('admin.user.canDisableCoverPhoto')) {
			$data['data']['disableCoverPhoto'] = $this->disableCoverPhoto;
			$data['data']['disableCoverPhotoReason'] = $this->disableCoverPhotoReason;
			$data['data']['disableCoverPhotoExpires'] = $this->disableCoverPhotoExpires;
			
			if ($this->deleteCoverPhoto) {
				UserProfileRuntimeCache::getInstance()->getObject($this->userID)->getCoverPhoto()->delete();
				
				$data['data']['coverPhotoHash'] = null;
				$data['data']['coverPhotoExtension'] = '';
				
				UserProfileRuntimeCache::getInstance()->removeObject($this->userID);
			}
		}
		
		$this->objectAction = new UserAction([$this->userID], 'update', $data);
		$this->objectAction->executeAction();
		
		// update user rank
		$editor = new UserEditor(new User($this->userID));
		if (MODULE_USER_RANK) {
			$action = new UserProfileAction([$editor], 'updateUserRank');
			$action->executeAction();
		}
		if (MODULE_USERS_ONLINE) {
			$action = new UserProfileAction([$editor], 'updateUserOnlineMarking');
			$action->executeAction();
		}
		
		// remove assignments
		$sql = "DELETE FROM	wcf".WCF_N."_moderation_queue_to_user
			WHERE		userID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$this->user->userID]);
		
		// reset moderation count
		ModerationQueueManager::getInstance()->resetModerationCount($this->user->userID);
		$this->saved();
		
		// reset password
		$this->password = $this->confirmPassword = '';
		
		// reload user when deleting the cover photo or disconnecting from 3rd party auth provider
		if ($this->deleteCoverPhoto || $this->disconnect3rdParty) $this->user = new User($this->userID);
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @inheritDoc
	 */
	protected function validateUsername($username) {
		if (mb_strtolower($this->user->username) != mb_strtolower($username)) {
			parent::validateUsername($username);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function validateEmail($email, $confirmEmail) {
		// check confirm input
		if (mb_strtolower($email) != mb_strtolower($confirmEmail)) {
			throw new UserInputException('confirmEmail', 'notEqual');
		}
		
		if (mb_strtolower($this->user->email) != mb_strtolower($email)) {
			parent::validateEmail($email, $this->confirmEmail);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function validatePassword($password, $confirmPassword) {
		if (!empty($password) || !empty($confirmPassword)) {
			parent::validatePassword($password, $confirmPassword);
		}
	}
	
	/**
	 * Validates the user avatar.
	 */
	protected function validateAvatar() {
		if ($this->avatarType != 'custom' && $this->avatarType != 'gravatar') $this->avatarType = 'none';
		
		try {
			switch ($this->avatarType) {
				case 'custom':
					if (!$this->user->avatarID) {
						throw new UserInputException('customAvatar');
					}
					break;
					
				case 'gravatar':
					if (!MODULE_GRAVATAR) {
						$this->avatarType = 'none';
						break;
					}
					
					// test gravatar
					if (!Gravatar::test($this->user->email)) {
						throw new UserInputException('gravatar', 'notFound');
					}
			}
		}
		catch (UserInputException $e) {
			$this->errorType[$e->getField()] = $e->getType();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		if ($this->user->userID == WCF::getUser()->userID && WCF::getUser()->hasOwnerAccess()) {
			$ownerGroupID = UserGroup::getOwnerGroupID();
			if ($ownerGroupID && !in_array($ownerGroupID, $this->groupIDs)) {
				// Members of the owner group cannot remove themselves.
				throw new PermissionDeniedException();
			}
		}
		
		$this->validateAvatar();
		
		parent::validate();
		
		if (!isset($this->availableStyles[$this->styleID])) {
			$this->styleID = 0;
		}
	}
}
