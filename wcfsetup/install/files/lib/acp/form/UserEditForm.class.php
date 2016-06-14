<?php
namespace wcf\acp\form;
use wcf\data\user\avatar\Gravatar;
use wcf\data\user\avatar\UserAvatar;
use wcf\data\user\avatar\UserAvatarAction;
use wcf\data\user\group\UserGroup;
use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\data\user\UserEditor;
use wcf\data\user\UserProfileAction;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\moderation\queue\ModerationQueueManager;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the user edit form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 */
class UserEditForm extends UserAddForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.user.management';
	
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
	 * @var	\wcf\data\user\UserEditor
	 */
	public $user = null;
	
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
	 * @var	string
	 */
	public $banExpires = '';
	
	/**
	 * user avatar object
	 * @var	\wcf\data\user\avatar\UserAvatar
	 */
	public $userAvatar = null;
	
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
	 * @var	string
	 */
	public $disableAvatarExpires = '';
	
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
		
		if (!WCF::getSession()->getPermission('admin.user.canEditPassword')) $this->password = $this->confirmPassword = '';
		if (!WCF::getSession()->getPermission('admin.user.canEditMailAddress')) $this->email = $this->confirmEmail = $this->user->email;
		
		if (!empty($_POST['banned'])) $this->banned = 1;
		if (isset($_POST['banReason'])) $this->banReason = StringUtil::trim($_POST['banReason']);
		if ($this->banned && !isset($_POST['banNeverExpires'])) {
			if (isset($_POST['banExpires'])) $this->banExpires = StringUtil::trim($_POST['banExpires']);
		}
		else {
			$this->banExpires = '';
		}
		
		if (isset($_POST['avatarType'])) $this->avatarType = $_POST['avatarType'];
		
		if (WCF::getSession()->getPermission('admin.user.canDisableAvatar')) {
			if (!empty($_POST['disableAvatar'])) $this->disableAvatar = 1;
			if (isset($_POST['disableAvatarReason'])) $this->disableAvatarReason = StringUtil::trim($_POST['disableAvatarReason']);
			if ($this->disableAvatar && !isset($_POST['disableAvatarNeverExpires'])) {
				if (isset($_POST['disableAvatarExpires'])) $this->disableAvatarExpires = StringUtil::trim($_POST['disableAvatarExpires']);
			}
			else {
				$this->disableAvatarExpires = '';
			}
		}
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
		
		parent::readData();
		
		// get avatar object
		if ($this->avatarType == 'custom') {
			$this->userAvatar = new UserAvatar($this->user->avatarID);
		}
	}
	
	/**
	 * Gets the selected languages.
	 */
	protected function readVisibleLanguages() {
		$this->visibleLanguages = $this->user->getLanguageIDs();
	}
	
	/**
	 * Gets the default values.
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
		
		$this->signature = $this->user->signature;
		$this->signatureEnableBBCodes = $this->user->signatureEnableBBCodes;
		$this->signatureEnableSmilies = $this->user->signatureEnableSmilies;
		$this->signatureEnableHtml = $this->user->signatureEnableHtml;
		$this->disableSignature = $this->user->disableSignature;
		$this->disableSignatureReason = $this->user->disableSignatureReason;
		$this->disableSignatureExpires = $this->user->disableSignatureExpires;
		$this->disableAvatar = $this->user->disableAvatar;
		$this->disableAvatarReason = $this->user->disableAvatarReason;
		$this->disableAvatarExpires = $this->user->disableAvatarExpires;
		
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
			'banExpires' => $this->banExpires
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
		
		// add default groups
		$defaultGroups = UserGroup::getAccessibleGroups([UserGroup::GUESTS, UserGroup::EVERYONE, UserGroup::USERS]);
		$oldGroupIDs = $this->user->getGroupIDs();
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
				'signature' => $this->signature,
				'signatureEnableBBCodes' => $this->signatureEnableBBCodes,
				'signatureEnableSmilies' => $this->signatureEnableSmilies,
				'signatureEnableHtml' => $this->signatureEnableHtml
			]),
			'groups' => $this->groupIDs,
			'languageIDs' => $this->visibleLanguages,
			'options' => $saveOptions
		];

		// handle ban
		if (WCF::getSession()->getPermission('admin.user.canBanUser')) {
			if ($this->banExpires) {
				$this->banExpires = strtotime($this->banExpires);
			}
			else {
				$this->banExpires = 0;
			}
			
			$data['data']['banned'] = $this->banned;
			$data['data']['banReason'] = $this->banReason;
			$data['data']['banExpires'] = $this->banExpires;
		}
		
		// handle disabled signature
		if (WCF::getSession()->getPermission('admin.user.canDisableSignature')) {
			if ($this->disableSignatureExpires) {
				$this->disableSignatureExpires = strtotime($this->disableSignatureExpires);
			}
			else {
				$this->disableSignatureExpires = 0;
			}
			
			$data['data']['disableSignature'] = $this->disableSignature;
			$data['data']['disableSignatureReason'] = $this->disableSignatureReason;
			$data['data']['disableSignatureExpires'] = $this->disableSignatureExpires;
		}
		
		// handle disabled avatar
		if (WCF::getSession()->getPermission('admin.user.canDisableAvatar')) {
			if ($this->disableAvatarExpires) {
				$this->disableAvatarExpires = strtotime($this->disableAvatarExpires);
			}
			else {
				$this->disableAvatarExpires = 0;
			}
			
			$data['data']['disableAvatar'] = $this->disableAvatar;
			$data['data']['disableAvatarReason'] = $this->disableAvatarReason;
			$data['data']['disableAvatarExpires'] = $this->disableAvatarExpires;
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
		$this->validateAvatar();
		
		parent::validate();
	}
}
