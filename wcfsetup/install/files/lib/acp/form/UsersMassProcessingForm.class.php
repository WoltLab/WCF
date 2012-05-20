<?php
namespace wcf\acp\form;
use wcf\data\option\Option;
use wcf\data\user\group\UserGroup;
use wcf\data\user\User;
use wcf\data\user\UserEditor;
use wcf\form\AbstractForm;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\event\EventHandler;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\language\LanguageFactory;
use wcf\system\menu\acp\ACPMenu;
use wcf\system\request\LinkHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;
use wcf\system\WCFACP;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;
		
/**
 * Shows the users mass processing form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category 	Community Framework
 */
class UsersMassProcessingForm extends UserOptionListForm {
	// system
	public $templateName = 'usersMassProcessing';
	public $neededPermissions = array('admin.user.canEditUser', 'admin.user.canDeleteUser', 'admin.user.canMailUser');
	
	// parameters
	public $username = '';
	public $email = '';
	public $groupIDArray = array();
	public $languageIDArray = array();
	public $invertGroupIDs = 0;
	
	// assign to group
	public $assignToGroupIDArray = array();
	
	// export mail address
	public $fileType = 'csv';
	public $separator = ',';
	public $textSeparator = '"'; 
	
	// send mail
	public $subject = '';
	public $text = '';
	public $from = '';
	public $enableHTML = 0;
	
	// data
	public $availableGroups = array();
	public $options = array();
	public $availableActions = array('sendMail', 'exportMailAddress', 'assignToGroup', 'delete');
	public $affectedUsers = 0;
	
	/**
	 * conditions builder object.
	 * 
	 * @var	wcf\system\database\condition\PreparedStatementConditionBuilder
	 */
	public $conditions = null;
	
	/**
	 * Options of the active category.
	 * 
	 * @var array
	 */
	public $activeOptions = array();
	
	/**
	 * @see wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['username'])) $this->username = StringUtil::trim($_POST['username']);
		if (isset($_POST['email'])) $this->email = StringUtil::trim($_POST['email']);
		if (isset($_POST['groupIDArray']) && is_array($_POST['groupIDArray'])) $this->groupIDArray = ArrayUtil::toIntegerArray($_POST['groupIDArray']);
		if (isset($_POST['languageIDArray']) && is_array($_POST['languageIDArray'])) $this->languageIDArray = ArrayUtil::toIntegerArray($_POST['languageIDArray']);
		if (isset($_POST['invertGroupIDs'])) $this->invertGroupIDs = intval($_POST['invertGroupIDs']);
		// assign to group
		if (isset($_POST['assignToGroupIDArray']) && is_array($_POST['assignToGroupIDArray'])) $this->assignToGroupIDArray = ArrayUtil::toIntegerArray($_POST['assignToGroupIDArray']);
		// export mail address
		if (isset($_POST['fileType']) && $_POST['fileType'] == 'xml') $this->fileType = $_POST['fileType'];
		if (isset($_POST['separator'])) $this->separator = $_POST['separator'];
		if (isset($_POST['textSeparator'])) $this->textSeparator = $_POST['textSeparator'];
		// send mail
		if (isset($_POST['subject'])) $this->subject = StringUtil::trim($_POST['subject']);
		if (isset($_POST['text'])) $this->text = StringUtil::trim($_POST['text']);
		if (isset($_POST['from'])) $this->from = StringUtil::trim($_POST['from']);
		if (isset($_POST['enableHTML'])) $this->enableHTML = intval($_POST['enableHTML']);
	}
	
	/**
	 * @see wcf\form\IForm::validate()
	 */
	public function validate() {
		AbstractForm::validate();

		// action
		if (!in_array($this->action, $this->availableActions)) {
			throw new UserInputException('action');
		}
		
		// assign to group
		if ($this->action == 'assignToGroup') {
			if (!count($this->assignToGroupIDArray)) {
				throw new UserInputException('assignToGroupIDArray');
			}
		}
		
		// send mail
		if ($this->action == 'sendMail') {
			if (empty($this->subject)) {
				throw new UserInputException('subject');
			}
			
			if (empty($this->text)) {
				throw new UserInputException('text');
			}
			
			if (empty($this->from)) {
				throw new UserInputException('from');
			}
		}
	}
	
	/**
	 * @see wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		// build conditions
		$this->conditions = new PreparedStatementConditionBuilder();
		// static fields
		if (!empty($this->username)) {
			$this->conditions->add("user.username LIKE ?", array('%'.addcslashes($this->username, '_%').'%'));
		}
		if (!empty($this->email)) {
			$this->conditions->add("user.email LIKE ?", array('%'.addcslashes($this->email, '_%').'%'));
		}
		if (count($this->groupIDArray) > 0) {
			$this->conditions->add("user.userID ".($this->invertGroupIDs == 1 ? 'NOT ' : '')."IN (SELECT userID FROM wcf".WCF_N."_user_to_group WHERE groupID IN (?))", array($this->groupIDArray));
		}
		if (count($this->languageIDArray) > 0) {
			$this->conditions->add("user.languageID IN (?)", array($this->languageIDArray));
		}
		
		// dynamic fields
		foreach ($this->activeOptions as $name => $option) {
			$value = isset($this->values[$option['optionName']]) ? $this->values[$option['optionName']] : null;
			$this->getTypeObject($option['optionType'])->getCondition($this->conditions, $option, $value);
		}

		// call buildConditions event
		EventHandler::getInstance()->fireAction($this, 'buildConditions');
		
		// execute action
		switch ($this->action) {
			case 'sendMail':
				WCF::getSession()->checkPermissions(array('admin.user.canMailUser'));
				// get user ids
				$userIDArray = array();
				$sql = "SELECT		user.userID
					FROM		wcf".WCF_N."_user
					LEFT JOIN	wcf".WCF_N."_user_option_value option_value
					ON		(option_value.userID = user.userID)".
					$this->conditions;
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute($this->conditions->getParameters());
				while ($row = $statement->fetchArray()) {
					$userIDArray[] = $row['userID'];
					$this->affectedUsers++;
				}

				// save config in session
				$userMailData = WCF::getSession()->getVar('userMailData');
				if ($userMailData === null) $userMailData = array();
				$mailID = count($userMailData);
				$userMailData[$mailID] = array(
					'action' => '',
					'userIDs' => implode(',', $userIDArray),
					'groupIDs' => '',
					'subject' => $this->subject,
					'text' => $this->text,
					'from' => $this->from,
					'enableHTML' => $this->enableHTML
				);
				WCF::getSession()->register('userMailData', $userMailData);
				$this->saved();
				
				$url = LinkHandler::getInstance()->getLink('UserMail', array('id' => $mailID));
				
				// show worker template
				WCF::getTPL()->assign(array(
					'pageTitle' => WCF::getLanguage()->get('wcf.acp.user.sendMail'),
					'url' => $url
				));
				WCF::getTPL()->display('worker');
				exit;
				break;
				
			case 'exportMailAddress':
				WCF::getSession()->checkPermissions(array('admin.user.canMailUser'));
				// send content type
				header('Content-Type: text/'.$this->fileType.'; charset=UTF-8');
				header('Content-Disposition: attachment; filename="export.'.$this->fileType.'"');
				
				if ($this->fileType == 'xml') {
					echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<addresses>\n";
				}
				
				// count users
				$sql = "SELECT		COUNT(*) AS count
					FROM		wcf".WCF_N."_user user
					LEFT JOIN	wcf".WCF_N."_user_option_value option_value
					ON		(option_value.userID = user.userID)
					".$this->conditions;
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute($this->conditions->getParameters());
				$count = $statement->fetchArray();
				
				// get users
				$sql = "SELECT		user.email
					FROM		wcf".WCF_N."_user user
					LEFT JOIN	wcf".WCF_N."_user_option_value option_value
					ON		(option_value.userID = user.userID)
					".$this->conditions."
					ORDER BY	user.email";
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute($this->conditions->getParameters());
				
				$i = 0;
				while ($row = $statement->fetchArray()) {
					if ($this->fileType == 'xml') echo "<address><![CDATA[".StringUtil::escapeCDATA($row['email'])."]]></address>\n";
					else echo $this->textSeparator . $row['email'] . $this->textSeparator . ($i < $count['count'] ? $this->separator : '');
					$i++;
					$this->affectedUsers++;
				}
				
				if ($this->fileType == 'xml') {
					echo "</addresses>";
				}
				$this->saved();
				exit;
				break;
				
			case 'assignToGroup':
				WCF::getSession()->checkPermissions(array('admin.user.canEditUser'));
				
				$userIDArray = $this->fetchUsers(function($userID, array $userData) {
					$user = new UserEditor(new User(null, $userData));
					$user->addToGroups($this->assignToGroupIDArray, false, false);
				});
				
				UserStorageHandler::getInstance()->reset($userIDArray, 'groupIDs', 1);
				break;
				
			case 'delete':
				WCF::getSession()->checkPermissions(array('admin.user.canDeleteUser'));
				
				$userIDArray = $this->fetchUsers();
				
				UserEditor::deleteUsers($userIDArray);
				break;
		}
		$this->saved();
		
		WCF::getTPL()->assign('affectedUsers', $this->affectedUsers);
	}
	
	protected function fetchUsers($loopFunction = null) {
		// select users
		$sql = "SELECT	user.*
			FROM	wcf".WCF_N."_user user
			LEFT JOIN	wcf".WCF_N."_user_option_value option_value
			ON		(option_value.userID = user.userID)
			".$this->conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->conditions->getParameters()));
		
		$users = array();
		while ($row = $statement->fetchArray()) {
			$users[$row['userID']] = $row;
		}
		
		// select group ids
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("userID = ?", array(array_keys($users)));
		
		$sql = "SELECT	userID, groupID
			FROM	wcf".WCF_N."_user_to_group
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		
		$groupIDs = array();
		while ($row = $statement->fetchArray()) {
			if (!is_array($groupIDs[$row['userID']])) {
				$groupIDs[$row['userID']] = array();
			}
			
			$groupIDs[$row['userID']][] = $row['groupID'];
		}
		
		
		foreach ($users as $userID => $userData) {
			if (!UserGroup::isAccessibleGroup($groupIDs[$userID])) {
				throw new PermissionDeniedException();
			}
			
			if ($loopFunction !== null) {
				$loopFunction($userID, $userData);
			}
			
			$userIDArray[] = $userID;
			$this->affectedUsers++;
		}
		
		return $userIDArray;
	}
	
	/**
	 * @see wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		if (!count($_POST)) {
			if (MAIL_USE_FORMATTED_ADDRESS)	$this->from = MAIL_FROM_NAME . ' <' . MAIL_FROM_ADDRESS . '>';
			else $this->from = MAIL_FROM_ADDRESS;
		}
		
		$this->availableGroups = $this->getAvailableGroups();
		
		foreach ($this->activeOptions as $name => $option) {
			if (isset($this->values[$name])) {
				$this->activeOptions[$name]['optionValue'] = $this->values[$name];
			}
		}
		
		$this->options = $this->optionHandler->getCategoryOptions('profile');
	}
	
	/**
	 * @see wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'username' => $this->username,
			'email' => $this->email,
			'groupIDArray' => $this->groupIDArray,
			'languageIDArray' => $this->languageIDArray,
			'invertGroupIDs' => $this->invertGroupIDs,
			'availableGroups' => $this->availableGroups,
			'availableLanguages' => LanguageFactory::getInstance()->getLanguages(),
			'options' => $this->options,
			'availableActions' => $this->availableActions,
			// assign to group
			'assignToGroupIDArray' => $this->assignToGroupIDArray,
			// export mail address
			'separator' => $this->separator,
			'textSeparator' => $this->textSeparator,
			'fileType' => $this->fileType,
			// send mail
			'subject' => $this->subject,
			'text' => $this->text,
			'from' => $this->from,
			'enableHTML' => $this->enableHTML
		));
	}
	
	/**
	 * @see wcf\form\IForm::show()
	 */
	public function show() {
		// set active menu item
		ACPMenu::getInstance()->setActiveMenuItem('wcf.acp.menu.link.user.massProcessing');
		
		// check master password
		WCFACP::checkMasterPassword();
		
		// show form
		parent::show();
	}
}
