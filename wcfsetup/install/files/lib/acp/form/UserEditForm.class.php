<?php
namespace wcf\acp\form;
use wcf\data\user\group\UserGroup;
use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\data\user\UserEditor;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the user edit form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
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
	 * @see	wcf\acp\form\AbstractOptionListForm::$loadActiveOptions
	 */
	public $loadActiveOptions = false;
	
	/**
	 * @see	wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->userID = intval($_REQUEST['id']);
		
		$user = new User($this->userID);
		if (!$user->userID) {
			throw new IllegalLinkException();
		}
		
		$this->user = new UserEditor($user);
		if (!UserGroup::isAccessibleGroup($this->user->getGroupIDs())) {
			throw new PermissionDeniedException();
		}
		
		$this->optionHandler->setUser($this->user->getDecoratedObject());
		$this->optionHandler->showEmptyOptions();
	}
	
	/**
	 * @see	wcf\page\IPage::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (!WCF::getSession()->getPermission('admin.user.canEditPassword')) $this->password = $this->confirmPassword = '';
		if (!WCF::getSession()->getPermission('admin.user.canEditMailAddress')) $this->email = $this->confirmEmail = $this->user->email;
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
	}
	
	/**
	 * @see	wcf\acp\form\UserAddForm::readOptionTree()
	 */
	protected function readOptionTree() {
		if (empty($_POST)) {
			$this->optionHandler->setUser($this->user->getDecoratedObject());
		}
		
		parent::readOptionTree();
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
			'user' => $this->user
		));
	}
	
	/**
	 * @see	wcf\form\IForm::save()
	 */
	public function save() {
		AbstractForm::save();
		
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
		$data = array(
			'data' => array_merge($this->additionalFields, array(
				'username' => $this->username,
				'email' => $this->email,
				'password' => $this->password,
			)),
			'groups' => $this->groupIDs,
			'languages' => $this->visibleLanguages,
			'options' => $saveOptions
		);
		$this->objectAction = new UserAction(array($this->userID), 'update', $data);
		$this->objectAction->executeAction();
		
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
		if (StringUtil::toLowerCase($this->user->username) != StringUtil::toLowerCase($username)) {
			parent::validateUsername($username);
		}
	}
	
	/**
	 * @see	wcf\acp\form\UserAddForm::validateEmail()
	 */
	protected function validateEmail($email, $confirmEmail) {
		if (StringUtil::toLowerCase($this->user->email) != StringUtil::toLowerCase($email)) {
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
}
