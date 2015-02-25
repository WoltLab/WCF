<?php
namespace wcf\acp\form;
use wcf\data\user\group\UserGroup;
use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\data\user\UserEditor;
use wcf\form\AbstractForm;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\event\EventHandler;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\language\LanguageFactory;
use wcf\system\menu\acp\ACPMenu;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;
use wcf\system\WCFACP;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;

/**
 * Shows the user bulk processing form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class UserBulkProcessingForm extends UserOptionListForm {
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.user.canEditUser', 'admin.user.canDeleteUser', 'admin.user.canMailUser');
	
	/**
	 * searched username
	 * @var	string
	 */
	public $username = '';
	
	/**
	 * searched email adress
	 * @var	string
	 */
	public $email = '';
	
	/**
	 * ids of the searched user group ids
	 * @var	array<integer>
	 */
	public $groupIDs = array();
	
	/**
	 * ids of the users' languages
	 * @var	array<integer>
	 */
	public $languageIDs = array();
	
	/**
	 * indicates if the user may not be in the user groups with the selected
	 * ids
	 * @var	integer
	 */
	public $invertGroupIDs = 0;
	
	/**
	 * registration start date
	 * @var	string
	 */
	public $registrationDateStart = '';
	
	/**
	 * registration start date
	 * @var	string
	 */
	public $registrationDateEnd = '';
	
	/**
	 * banned state
	 * @var	boolean
	 */
	public $banned = 0;
	
	/**
	 * not banned state
	 * @var	boolean
	 */
	public $notBanned = 0;
	
	/**
	 * last activity start time
	 * @var	string
	 */
	public $lastActivityTimeStart = '';
	
	/**
	 * last activity end time
	 * @var	string
	 */
	public $lastActivityTimeEnd = '';
	
	/**
	 * enabled state
	 * @var	boolean
	 */
	public $enabled = 0;
	
	/**
	 * disabled state
	 * @var	boolean
	 */
	public $disabled = 0;
	
	// assign to group
	public $assignToGroupIDs = array();
	
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
	 * conditions builder object
	 * @var	\wcf\system\database\condition\PreparedStatementConditionBuilder
	 */
	public $conditions = null;
	
	/**
	 * options of the active category
	 * @var	array
	 */
	public $activeOptions = array();
	
	/**
	 * @see	\wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['username'])) $this->username = StringUtil::trim($_POST['username']);
		if (isset($_POST['email'])) $this->email = StringUtil::trim($_POST['email']);
		if (isset($_POST['groupIDs']) && is_array($_POST['groupIDs'])) $this->groupIDs = ArrayUtil::toIntegerArray($_POST['groupIDs']);
		if (isset($_POST['languageIDs']) && is_array($_POST['languageIDs'])) $this->languageIDs = ArrayUtil::toIntegerArray($_POST['languageIDs']);
		if (isset($_POST['invertGroupIDs'])) $this->invertGroupIDs = intval($_POST['invertGroupIDs']);
		if (isset($_POST['registrationDateStart'])) $this->registrationDateStart = $_POST['registrationDateStart'];
		if (isset($_POST['registrationDateEnd'])) $this->registrationDateEnd = $_POST['registrationDateEnd'];
		if (isset($_POST['banned'])) $this->banned = intval($_POST['banned']);
		if (isset($_POST['notBanned'])) $this->notBanned = intval($_POST['notBanned']);
		if (isset($_POST['lastActivityTimeStart'])) $this->lastActivityTimeStart = $_POST['lastActivityTimeStart'];
		if (isset($_POST['lastActivityTimeEnd'])) $this->lastActivityTimeEnd = $_POST['lastActivityTimeEnd'];
		if (isset($_POST['enabled'])) $this->enabled = intval($_POST['enabled']);
		if (isset($_POST['disabled'])) $this->disabled = intval($_POST['disabled']);
		
		// assign to group
		if (isset($_POST['assignToGroupIDs']) && is_array($_POST['assignToGroupIDs'])) $this->assignToGroupIDs = ArrayUtil::toIntegerArray($_POST['assignToGroupIDs']);
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
	 * @see	\wcf\form\IForm::validate()
	 */
	public function validate() {
		AbstractForm::validate();
		
		// action
		if (!in_array($this->action, $this->availableActions)) {
			throw new UserInputException('action');
		}
		
		// assign to group
		if ($this->action == 'assignToGroup') {
			if (empty($this->assignToGroupIDs)) {
				throw new UserInputException('assignToGroupIDs');
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
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		// build conditions
		$this->conditions = new PreparedStatementConditionBuilder();
		
		// deny self delete
		if ($this->action == 'delete') {
			$this->conditions->add("user_table.userID <> ?", array(WCF::getUser()->userID));
		}
		
		// static fields
		if (!empty($this->username)) {
			$this->conditions->add("user_table.username LIKE ?", array('%'.addcslashes($this->username, '_%').'%'));
		}
		if (!empty($this->email)) {
			$this->conditions->add("user_table.email LIKE ?", array('%'.addcslashes($this->email, '_%').'%'));
		}
		if (!empty($this->groupIDs)) {
			$this->conditions->add("user_table.userID ".($this->invertGroupIDs == 1 ? 'NOT ' : '')."IN (SELECT userID FROM wcf".WCF_N."_user_to_group WHERE groupID IN (?))", array($this->groupIDs));
		}
		if (!empty($this->languageIDs)) {
			$this->conditions->add("user_table.languageID IN (?)", array($this->languageIDs));
		}
		
		// registration date
		if ($startDate = @strtotime($this->registrationDateStart)) {
			$this->conditions->add('user_table.registrationDate >= ?', array($startDate));
		}
		if ($endDate = @strtotime($this->registrationDateEnd)) {
			$this->conditions->add('user_table.registrationDate <= ?', array($endDate));
		}
		
		if ($this->banned) {
			$this->conditions->add('user_table.banned = ?', array(1));
		}
		if ($this->notBanned) {
			$this->conditions->add('user_table.banned = ?', array(0));
		}
		
		// last activity time
		if ($startDate = @strtotime($this->lastActivityTimeStart)) {
			$this->conditions->add('user_table.lastActivityTime >= ?', array($startDate));
		}
		if ($endDate = @strtotime($this->lastActivityTimeEnd)) {
			$this->conditions->add('user_table.lastActivityTime <= ?', array($endDate));
		}
		
		if ($this->enabled) {
			$this->conditions->add('user_table.activationCode = ?', array(0));
		}
		if ($this->disabled) {
			$this->conditions->add('user_table.activationCode <> ?', array(0));
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
				$userIDs = array();
				$sql = "SELECT		user_table.userID
					FROM		wcf".WCF_N."_user user_table
					LEFT JOIN	wcf".WCF_N."_user_option_value option_value
					ON		(option_value.userID = user_table.userID)".
					$this->conditions;
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute($this->conditions->getParameters());
				while ($row = $statement->fetchArray()) {
					$userIDs[] = $row['userID'];
					$this->affectedUsers++;
				}
				
				if (!empty($userIDs)) {
					// save config in session
					$userMailData = WCF::getSession()->getVar('userMailData');
					if ($userMailData === null) $userMailData = array();
					$mailID = count($userMailData);
					$userMailData[$mailID] = array(
						'action' => '',
						'userIDs' => $userIDs,
						'groupIDs' => '',
						'subject' => $this->subject,
						'text' => $this->text,
						'from' => $this->from,
						'enableHTML' => $this->enableHTML
					);
					WCF::getSession()->register('userMailData', $userMailData);
					
					WCF::getTPL()->assign('mailID', $mailID);
				}
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
					FROM		wcf".WCF_N."_user user_table
					LEFT JOIN	wcf".WCF_N."_user_option_value option_value
					ON		(option_value.userID = user_table.userID)
					".$this->conditions;
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute($this->conditions->getParameters());
				$count = $statement->fetchArray();
				
				// get users
				$sql = "SELECT		user_table.email
					FROM		wcf".WCF_N."_user user_table
					LEFT JOIN	wcf".WCF_N."_user_option_value option_value
					ON		(option_value.userID = user_table.userID)
					".$this->conditions."
					ORDER BY	user_table.email";
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
				
				$_this = $this;
				$userIDs = $this->fetchUsers(function($userID, array $userData) use ($_this) {
					$user = new UserEditor(new User(null, $userData));
					$user->addToGroups($_this->assignToGroupIDs, false, false);
				});
				
				if (!empty($userIDs)) {
					UserStorageHandler::getInstance()->reset($userIDs, 'groupIDs', 1);
				}
			break;
			
			case 'delete':
				WCF::getSession()->checkPermissions(array('admin.user.canDeleteUser'));
				
				$userIDs = $this->fetchUsers();
				
				if (!empty($userIDs)) {
					$userAction = new UserAction($userIDs, 'delete');
					$userAction->executeAction();
				}
			break;
		}
		$this->saved();
		
		WCF::getTPL()->assign('affectedUsers', $this->affectedUsers);
	}
	
	/**
	 * Fetches a list of users.
	 * 
	 * @param	mixed		$loopFunction
	 * @return	array<integer>
	 */
	public function fetchUsers($loopFunction = null) {
		// select users
		$sql = "SELECT		user_table.*
			FROM		wcf".WCF_N."_user user_table
			LEFT JOIN	wcf".WCF_N."_user_option_value option_value
			ON		(option_value.userID = user_table.userID)
			".$this->conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($this->conditions->getParameters());
		
		$users = array();
		while ($row = $statement->fetchArray()) {
			$users[$row['userID']] = $row;
		}
		if (empty($users)) return array();
		
		// select group ids
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("userID IN (?)", array(array_keys($users)));
		
		$sql = "SELECT	userID, groupID
			FROM	wcf".WCF_N."_user_to_group
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		
		$groupIDs = array();
		while ($row = $statement->fetchArray()) {
			if (!isset($groupIDs[$row['userID']])) {
				$groupIDs[$row['userID']] = array();
			}
			
			$groupIDs[$row['userID']][] = $row['groupID'];
		}
		
		foreach ($users as $userID => $userData) {
			if (!empty($groupIDs[$userID]) && !UserGroup::isAccessibleGroup($groupIDs[$userID])) {
				throw new PermissionDeniedException();
			}
			
			if ($loopFunction !== null) {
				$loopFunction($userID, $userData);
			}
			
			$userIDs[] = $userID;
			$this->affectedUsers++;
		}
		
		return $userIDs;
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		if (empty($_POST)) {
			if (MAIL_USE_FORMATTED_ADDRESS) {
				$this->from = MAIL_FROM_NAME.' <'.MAIL_FROM_ADDRESS.'>';
			}
			else {
				$this->from = MAIL_FROM_ADDRESS;
			}
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
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'username' => $this->username,
			'email' => $this->email,
			'groupIDs' => $this->groupIDs,
			'languageIDs' => $this->languageIDs,
			'invertGroupIDs' => $this->invertGroupIDs,
			'registrationDateStart' => $this->registrationDateStart,
			'registrationDateEnd' => $this->registrationDateEnd,
			'banned' => $this->banned,
			'notBanned' => $this->notBanned,
			'lastActivityTimeStart' => $this->lastActivityTimeStart,
			'lastActivityTimeEnd' => $this->lastActivityTimeEnd,
			'enabled' => $this->enabled,
			'disabled' => $this->disabled,
			
			'availableGroups' => $this->availableGroups,
			'availableLanguages' => LanguageFactory::getInstance()->getLanguages(),
			'options' => $this->options,
			'availableActions' => $this->availableActions,
			// assign to group
			'assignToGroupIDs' => $this->assignToGroupIDs,
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
	 * @see	\wcf\form\IForm::show()
	 */
	public function show() {
		// set active menu item
		ACPMenu::getInstance()->setActiveMenuItem('wcf.acp.menu.link.user.bulkProcessing');
		
		// check master password
		WCFACP::checkMasterPassword();
		
		// show form
		parent::show();
	}
}
