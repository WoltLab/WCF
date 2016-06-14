<?php
namespace wcf\acp\form;
use wcf\data\user\group\UserGroup;
use wcf\data\user\UserAction;
use wcf\form\AbstractForm;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\UserInputException;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;
use wcf\util\UserUtil;

/**
 * Shows the user add form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 */
class UserAddForm extends UserOptionListForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.user.add';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.user.canAddUser'];
	
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
	 * @var	integer[]
	 */
	public $groupIDs = [];
	
	/**
	 * language id
	 * @var	integer
	 */
	public $languageID = 0;
	
	/**
	 * visible languages
	 * @var	integer[]
	 */
	public $visibleLanguages = [];
	
	/**
	 * title of the user
	 * @var	string
	 */
	protected $userTitle = '';
	
	/**
	 * signature text
	 * @var	string
	 */
	public $signature = '';
	
	/**
	 * enables smilies
	 * @var	boolean
	 */
	public $signatureEnableSmilies = 1;
	
	/**
	 * enables bbcodes
	 * @var	boolean
	 */
	public $signatureEnableBBCodes = 1;
	
	/**
	 * enables html
	 * @var	boolean
	 */
	public $signatureEnableHtml = 0;
	
	/**
	 * true to disable this signature
	 * @var	boolean
	 */
	public $disableSignature = 0;
	
	/**
	 * reason
	 * @var	string
	 */
	public $disableSignatureReason = '';
	
	/**
	 * date when the signature will be enabled again
	 * @var	string
	 */
	public $disableSignatureExpires = '';
	
	/**
	 * tree of available user options
	 * @var	array
	 */
	public $optionTree = [];
	
	/**
	 * @inheritDoc
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
		if (isset($_POST['userTitle'])) $this->userTitle = $_POST['userTitle'];
		
		if (isset($_POST['signature'])) $this->signature = StringUtil::trim($_POST['signature']);
		
		$this->signatureEnableBBCodes = $this->signatureEnableSmilies = 0;
		if (!empty($_POST['signatureEnableBBCodes'])) $this->signatureEnableBBCodes = 1;
		if (!empty($_POST['signatureEnableSmilies'])) $this->signatureEnableSmilies = 1;
		if (!empty($_POST['signatureEnableHtml'])) $this->signatureEnableHtml = 1;
		
		if (WCF::getSession()->getPermission('admin.user.canDisableSignature')) {
			if (isset($_POST['disableSignatureReason'])) $this->disableSignatureReason = StringUtil::trim($_POST['disableSignatureReason']);
			if (!empty($_POST['disableSignature'])) $this->disableSignature = 1;
			if ($this->disableSignature && !isset($_POST['disableSignatureNeverExpires'])) {
				if (isset($_POST['disableSignatureExpires'])) $this->disableSignatureExpires = StringUtil::trim($_POST['disableSignatureExpires']);
			}
			else {
				$this->disableSignatureExpires = '';
			}
		}
	}
	
	/**
	 * @inheritDoc
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
			$conditions->add("groupID IN (?)", [$this->groupIDs]);
			$conditions->add("groupType NOT IN (?)", [[UserGroup::GUESTS, UserGroup::EVERYONE, UserGroup::USERS]]);
			
			$sql = "SELECT	groupID
				FROM	wcf".WCF_N."_user_group
				".$conditions;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());
			$this->groupIDs = [];
			while ($row = $statement->fetchArray()) {
				if (UserGroup::isAccessibleGroup([$row['groupID']])) {
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
		
		// validate user title
		try {
			if (mb_strlen($this->userTitle) > USER_TITLE_MAX_LENGTH) {
				throw new UserInputException('userTitle', 'tooLong');
			}
			if (!StringUtil::executeWordFilter($this->userTitle, USER_FORBIDDEN_TITLES)) {
				throw new UserInputException('userTitle', 'forbidden');
			}
		}
		catch (UserInputException $e) {
			$this->errorType[$e->getField()] = $e->getType();
		}
		
		// validate dynamic options
		parent::validate();
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		AbstractForm::save();
		
		// create
		$saveOptions = $this->optionHandler->save();
		$this->additionalFields['languageID'] = $this->languageID;
		$data = [
			'data' => array_merge($this->additionalFields, [
				'username' => $this->username,
				'email' => $this->email,
				'password' => $this->password,
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
		
		if (WCF::getSession()->getPermission('admin.user.canDisableSignature')) {
			$disableSignatureExpires = 0;
			if ($this->disableSignatureExpires) {
				$disableSignatureExpires = strtotime($this->disableSignatureExpires);
			}
			
			$data['data']['disableSignature'] = $this->disableSignature;
			$data['data']['disableSignatureReason'] = $this->disableSignatureReason;
			$data['data']['disableSignatureExpires'] = $disableSignatureExpires;
		}
		
		$this->objectAction = new UserAction([], 'create', $data);
		$this->objectAction->executeAction();
		$this->saved();
		
		// show empty add form
		WCF::getTPL()->assign([
			'success' => true
		]);
		
		// reset values
		$this->signatureEnableHtml = $this->disableSignature = 0;
		$this->signatureEnableSmilies = $this->signatureEnableBBCodes = 1;
		$this->username = $this->email = $this->confirmEmail = $this->password = $this->confirmPassword = $this->userTitle = '';
		$this->signature = $this->disableSignatureReason = $this->disableSignatureExpires = '';
		$this->groupIDs = [];
		$this->languageID = $this->getDefaultFormLanguageID();
		/** @noinspection PhpUndefinedMethodInspection */
		$this->optionHandler->resetOptionValues();
	}
	
	/**
	 * Throws a UserInputException if the username is not unique or not valid.
	 * 
	 * @param	string		$username
	 * @throws	UserInputException
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
	 * @throws	UserInputException
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
		if (mb_strtolower($email) != mb_strtolower($confirmEmail)) {
			throw new UserInputException('confirmEmail', 'notEqual');
		}
	}
	
	/**
	 * Throws a UserInputException if the password is not valid.
	 * 
	 * @param	string		$password
	 * @param	string		$confirmPassword
	 * @throws	UserInputException
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
	 * @inheritDoc
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
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
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
			'action' => 'add',
			'userTitle' => $this->userTitle,
			'signature' => $this->signature,
			'signatureEnableBBCodes' => $this->signatureEnableBBCodes,
			'signatureEnableSmilies' => $this->signatureEnableSmilies,
			'signatureEnableHtml' => $this->signatureEnableHtml,
			'disableSignature' => $this->disableSignature,
			'disableSignatureReason' => $this->disableSignatureReason,
			'disableSignatureExpires' => $this->disableSignatureExpires
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function show() {
		// get the default language id
		$this->languageID = $this->getDefaultFormLanguageID();
		
		// show form
		parent::show();
	}
}
