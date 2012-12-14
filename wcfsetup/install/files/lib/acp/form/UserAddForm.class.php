<?php
namespace wcf\acp\form;
use wcf\data\user\group\UserGroup;
use wcf\data\user\UserAction;
use wcf\form\AbstractForm;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\UserInputException;
use wcf\system\language\LanguageFactory;
use wcf\system\menu\acp\ACPMenu;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;
use wcf\util\UserUtil;

/**
 * Shows the user add form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class UserAddForm extends UserOptionListForm {
	/**
	 * @see	wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.user.canAddUser');
	
	/**
	 * name of the active menu item
	 * @var	string
	 */
	public $menuItemName = 'wcf.acp.menu.link.user.add';
	
	/**
	 * username
	 * @var	string
	 */
	public $username = '';
	
	/**
	 * email address
	 * @var	string
	 */
	public $email = '';
	
	/**
	 * confirmed email address
	 * @var	string
	 */
	public $confirmEmail = '';
	
	/**
	 * user password
	 * @var	string
	 */
	public $password = '';
	
	/**
	 * confirmed user password
	 * @var	string
	 */
	public $confirmPassword = '';
	
	/**
	 * user group ids
	 * @var	array<integer>
	 */
	public $groupIDs = array();
	
	/**
	 * language id
	 * @var	integer
	 */
	public $languageID = 0;
	
	/**
	 * visible languages
	 * @var	array<integer>
	 */
	public $visibleLanguages = array();
	
	/**
	 * additional fields
	 * @var	array<mixed>
	 */
	public $additionalFields = array();
	
	/**
	 * @see	wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['username'])) $this->username = StringUtil::trim($_POST['username']);
		if (isset($_POST['email'])) $this->email = StringUtil::trim($_POST['email']);
		if (isset($_POST['confirmEmail'])) $this->confirmEmail = StringUtil::trim($_POST['confirmEmail']);
		if (isset($_POST['password'])) $this->password = $_POST['password'];
		if (isset($_POST['confirmPassword'])) $this->confirmPassword = $_POST['confirmPassword'];
		if (isset($_POST['groupIDs']) && is_array($_POST['groupIDs'])) $this->groupIDs = ArrayUtil::toIntegerArray($_POST['groupIDs']);
		if (isset($_POST['visibleLanguages']) && is_array($_POST['visibleLanguages'])) $this->visibleLanguages = ArrayUtil::toIntegerArray($_POST['visibleLanguages']);
		if (isset($_POST['languageID'])) $this->languageID = intval($_POST['languageID']);
	}
	
	/**
	 * @see	wcf\form\IForm::validate()
	 */
	public function validate() {
		// validate static user options 
		try {
			$this->validateUsername($this->username);
		}
		catch (UserInputException $e) {
			$this->errorType[$e->getField()] = $e->getType();
		}
		
		try {
			$this->validateEmail($this->email, $this->confirmEmail);
		}
		catch (UserInputException $e) {
			$this->errorType[$e->getField()] = $e->getType();
		}
		
		try {
			$this->validatePassword($this->password, $this->confirmPassword);
		}
		catch (UserInputException $e) {
			$this->errorType[$e->getField()] = $e->getType();
		}
		
		// validate user groups
		if (!empty($this->groupIDs)) {
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("groupID IN (?)", array($this->groupIDs));
			$conditions->add("groupType NOT IN (?)", array(array(UserGroup::GUESTS, UserGroup::EVERYONE, UserGroup::USERS)));
			
			$sql = "SELECT	groupID
				FROM	wcf".WCF_N."_user_group
				".$conditions;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());
			$this->groupIDs = array();
			while ($row = $statement->fetchArray()) {
				if (UserGroup::isAccessibleGroup(array($row['groupID']))) {
					$this->groupIDs[] = $row['groupID'];
				}
			}
		}
		
		// validate user language
		$language = LanguageFactory::getInstance()->getLanguage($this->languageID);
		if ($language === null || !$language->languageID) {
			// use default language
			$this->languageID = LanguageFactory::getInstance()->getDefaultLanguageID();
		}
		
		// validate visible languages
		foreach ($this->visibleLanguages as $key => $visibleLanguage) {
			$language = LanguageFactory::getInstance()->getLanguage($visibleLanguage);
			if (!$language->languageID || !$language->hasContent) {
				unset($this->visibleLanguages[$key]);
			}
		}
		if (empty($this->visibleLanguages) && ($language = LanguageFactory::getInstance()->getLanguage($this->languageID)) && $language->hasContent) {
			$this->visibleLanguages[] = $this->languageID;
		}
		
		// validate dynamic options
		parent::validate();
	}
	
	/**
	 * @see	wcf\form\IForm::save()
	 */
	public function save() {
		AbstractForm::save();
		
		// create
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
		$this->objectAction = new UserAction(array(), 'create', $data);
		$this->objectAction->executeAction();
		$this->saved();
		
		// show empty add form
		WCF::getTPL()->assign(array(
			'success' => true
		));
		
		// reset values
		$this->username = $this->email = $this->confirmEmail = $this->password = $this->confirmPassword = '';
		$this->groupIDs = array();
		$this->languageID = $this->getDefaultFormLanguageID();
		$this->optionHandler->resetOptionValues();
	}
	
	/**
	 * Throws a UserInputException if the username is not unique or not valid.
	 * 
	 * @param	string		$username
	 */
	protected function validateUsername($username) {
		if (empty($username)) {
			throw new UserInputException('username');
		}
		
		// check for forbidden chars (e.g. the ",")
		if (!UserUtil::isValidUsername($username)) {
			throw new UserInputException('username', 'notValid');
		}
		
		// Check if username exists already.
		if (!UserUtil::isAvailableUsername($username)) {
			throw new UserInputException('username', 'notUnique');
		}
	}
	
	/**
	 * Throws a UserInputException if the email is not unique or not valid.
	 * 
	 * @param	string		$email
	 * @param	string		$confirmEmail
	 */
	protected function validateEmail($email, $confirmEmail) {
		if (empty($email)) {	
			throw new UserInputException('email');
		}
		
		// check for valid email (one @ etc.)
		if (!UserUtil::isValidEmail($email)) {
			throw new UserInputException('email', 'notValid');
		}
		
		// Check if email exists already.
		if (!UserUtil::isAvailableEmail($email)) {
			throw new UserInputException('email', 'notUnique');
		}
		
		// check confirm input
		if (StringUtil::toLowerCase($email) != StringUtil::toLowerCase($confirmEmail)) {
			throw new UserInputException('confirmEmail', 'notEqual');
		}
	}
	
	/**
	 * Throws a UserInputException if the password is not valid.
	 * 
	 * @param	string		$password
	 * @param	string		$confirmPassword
	 */
	protected function validatePassword($password, $confirmPassword) {
		if (empty($password)) {
			throw new UserInputException('password');
		}
		
		// check confirm input
		if ($password != $confirmPassword) {
			throw new UserInputException('confirmPassword', 'notEqual');
		}
	}
	
	/**
	 * @see	wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->readOptionTree();
	}
	
	/**
	 * Reads option tree on page init.
	 */
	protected function readOptionTree() {
		$this->optionTree = $this->optionHandler->getOptionTree();
	}
	
	/**
	 * @see	wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'username' => $this->username,
			'email' => $this->email,
			'confirmEmail' => $this->confirmEmail,
			'password' => $this->password,
			'confirmPassword' => $this->confirmPassword,
			'groupIDs' => $this->groupIDs,
			'optionTree' => $this->optionTree,
			'availableGroups' => $this->getAvailableGroups(),
			'availableLanguages' => LanguageFactory::getInstance()->getLanguages(),
			'languageID' => $this->languageID,
			'visibleLanguages' => $this->visibleLanguages,
			'availableContentLanguages' => LanguageFactory::getInstance()->getContentLanguages(),
			'action' => 'add'
		));
	}
	
	/**
	 * @see	wcf\page\IPage::show()
	 */
	public function show() {
		// set active menu item
		ACPMenu::getInstance()->setActiveMenuItem($this->menuItemName);
		
		// get the default language id
		$this->languageID = $this->getDefaultFormLanguageID();
		
		// show form
		parent::show();
	}
}
