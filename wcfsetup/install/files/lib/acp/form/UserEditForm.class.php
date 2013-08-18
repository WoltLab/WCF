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
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class UserEditForm extends UserAddForm {
	/**
	 * @see	wcf\acp\form\UserAddForm::$menuItemName
	 */
	public $menuItemName = 'wcf.acp.menu.link.user.management';
	
	/**
	 * @see	wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.user.canEditUser');
	
	/**
	 * user id
	 * @var	integer
	 */
	public $userID = 0;
	
	/**
	 * user editor object
	 * @var	wcf\data\user\UserEditor
	 */
	public $user = null;
	
	/**
	 * ban status
	 * @var boolean
	 */
	public $banned = 0;
	
	/**
	 * ban reason
	 * @var string
	 */
	public $banReason = '';
	
	/**
	 * user avatar object
	 * @var wcf\data\user\avatar\UserAvatar
	 */
	public $userAvatar = null;
	
	/**
	 * avatar type
	 * @var	string
	 */
	public $avatarType = 'none';
	
	/**
	 * true to disable this avatar
	 * @var boolean
	 */
	public $disableAvatar = 0;
	
	/**
	 * reason
	 * @var string
	 */
	public $disableAvatarReason = '';
	
	/**
	 * @see	wcf\page\IPage::readParameters()
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
	 * wcf\acp\form\AbstractOptionListForm::initOptionHandler()
	 */
	protected function initOptionHandler() {
		$this->optionHandler->setUser($this->user->getDecoratedObject());
	}
	
	/**
	 * @see	wcf\page\IPage::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (!WCF::getSession()->getPermission('admin.user.canEditPassword')) $this->password = $this->confirmPassword = '';
		if (!WCF::getSession()->getPermission('admin.user.canEditMailAddress')) $this->email = $this->confirmEmail = $this->user->email;
		
		if (!empty($_POST['banned'])) $this->banned = 1;
		if (isset($_POST['banReason'])) $this->banReason = StringUtil::trim($_POST['banReason']);
		if (isset($_POST['avatarType'])) $this->avatarType = $_POST['avatarType'];
		if (!empty($_POST['disableAvatar'])) $this->disableAvatar = 1;
		if (isset($_POST['disableAvatarReason'])) $this->disableAvatarReason = StringUtil::trim($_POST['disableAvatarReason']);
	}
	
	/**
	 * @see	wcf\page\IPage::readData()
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
		$this->userTitle = $this->user->userTitle;
		
		$this->signature = $this->user->signature;
		$this->signatureEnableBBCodes = $this->user->signatureEnableBBCodes;
		$this->signatureEnableSmilies = $this->user->signatureEnableSmilies;
		$this->signatureEnableHtml = $this->user->signatureEnableHtml;
		$this->disableSignature = $this->user->disableSignature;
		$this->disableSignatureReason = $this->user->disableSignatureReason;
		$this->disableAvatar = $this->user->disableAvatar;
		$this->disableAvatarReason = $this->user->disableAvatarReason;
			
		if ($this->user->avatarID) $this->avatarType = 'custom';
		else if (MODULE_GRAVATAR && $this->user->enableGravatar) $this->avatarType = 'gravatar';
	}
	
	/**
	 * @see	wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
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
			'userAvatar' => $this->userAvatar
		));
	}
	
	/**
	 * @see	wcf\form\IForm::save()
	 */
	public function save() {
		AbstractForm::save();
		
		// handle avatar
		if ($this->avatarType != 'custom') {
			// delete custom avatar
			if ($this->user->avatarID) {
				$action = new UserAvatarAction(array($this->user->avatarID), 'delete');
				$action->executeAction();
			}
		}
		switch ($this->avatarType) {
			case 'none':
				$avatarData = array(
					'avatarID' => null,
					'enableGravatar' => 0
				);
				break;
		
			case 'custom':
				$avatarData = array(
					'enableGravatar' => 0
				);
				break;
		
			case 'gravatar':
				$avatarData = array(
					'avatarID' => null,
					'enableGravatar' => 1
				);
				break;
		}
		$avatarData['disableAvatar'] = $this->disableAvatar;
		$avatarData['disableAvatarReason'] = $this->disableAvatarReason;
		$this->additionalFields = array_merge($this->additionalFields, $avatarData);
		
		// add default groups
		$defaultGroups = UserGroup::getAccessibleGroups(array(UserGroup::GUESTS, UserGroup::EVERYONE, UserGroup::USERS));
		$oldGroupIDs = $this->user->getGroupIDs();
		foreach ($oldGroupIDs as $oldGroupID) {
			if (isset($defaultGroups[$oldGroupID])) {
				$this->groupIDs[] = $oldGroupID;
			}
		}
		$this->groupIDs = array_unique($this->groupIDs);
		
		// save user
		$saveOptions = $this->optionHandler->save();
		$this->additionalFields['languageID'] = $this->languageID;
		if (WCF::getSession()->getPermission('admin.user.canBanUser')) {
			$this->additionalFields['banned'] = $this->banned;
			$this->additionalFields['banReason'] = $this->banReason;
		}
		$data = array(
			'data' => array_merge($this->additionalFields, array(
				'username' => $this->username,
				'email' => $this->email,
				'password' => $this->password,
				'banned' => $this->banned,
				'banReason' => $this->banReason,
				'userTitle' => $this->userTitle,
				'signature' => $this->signature,
				'signatureEnableBBCodes' => $this->signatureEnableBBCodes,
				'signatureEnableSmilies' => $this->signatureEnableSmilies,
				'signatureEnableHtml' => $this->signatureEnableHtml,
				'disableSignature' => $this->disableSignature,
				'disableSignatureReason' => $this->disableSignatureReason
			)),
			'groups' => $this->groupIDs,
			'languages' => $this->visibleLanguages,
			'options' => $saveOptions
		);
		$this->objectAction = new UserAction(array($this->userID), 'update', $data);
		$this->objectAction->executeAction();
		
		// update user rank
		$editor = new UserEditor(new User($this->userID));
		if (MODULE_USER_RANK) {
			$action = new UserProfileAction(array($editor), 'updateUserRank');
			$action->executeAction();
		}
		if (MODULE_USERS_ONLINE) {
			$action = new UserProfileAction(array($editor), 'updateUserOnlineMarking');
			$action->executeAction();
		}
		
		// remove assignments
		$sql = "DELETE FROM	wcf".WCF_N."_moderation_queue_to_user
			WHERE		userID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->user->userID));
		
		// reset moderation count
		ModerationQueueManager::getInstance()->resetModerationCount($this->user->userID);
		$this->saved();
		
		// reset password
		$this->password = $this->confirmPassword = '';
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see	wcf\acp\form\UserAddForm::validateUsername()
	 */
	protected function validateUsername($username) {
		if (mb_strtolower($this->user->username) != mb_strtolower($username)) {
			parent::validateUsername($username);
		}
	}
	
	/**
	 * @see	wcf\acp\form\UserAddForm::validateEmail()
	 */
	protected function validateEmail($email, $confirmEmail) {
		if (mb_strtolower($this->user->email) != mb_strtolower($email)) {
			parent::validateEmail($email, $this->confirmEmail);
		}
	}
	
	/**
	 * @see	wcf\acp\form\UserAddForm::validatePassword()
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
	 * @see	wcf\form\IForm::validate()
	 */
	public function validate() {
		$this->validateAvatar();
		
		parent::validate();
	}
}
