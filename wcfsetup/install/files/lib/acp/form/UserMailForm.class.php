<?php
namespace wcf\acp\form;
use wcf\data\user\group\UserGroup;
use wcf\data\user\UserList;
use wcf\form\AbstractForm;
use wcf\system\clipboard\ClipboardHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;

/**
 * Shows the user mail form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class UserMailForm extends AbstractForm {
	/**
	 * enable html for message body
	 * @var	boolean
	 */
	public $enableHTML = false;
	
	/**
	 * sender name
	 * @var	string
	 */
	public $from = '';
	
	/**
	 * list of group ids
	 * @var	array<integer>
	 */
	public $groupIDs = array();
	
	/**
	 * list of groups
	 * @var	array<\wcf\data\user\group\UserGroup>
	 */
	public $groups = array();
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.user.canMailUser');
	
	/**
	 * message subject
	 * @var	string
	 */
	public $subject = '';
	
	/**
	 * message body
	 * @var	string
	 */
	public $text = '';
	
	/**
	 * list of user ids
	 * @var	array<integer>
	 */
	public $userIDs = array();
	
	/**
	 * list of users
	 * @var	\wcf\data\user\UserList
	 */
	public $userList = null;
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->activeMenuItem = ($this->action == 'all' ? 'wcf.acp.menu.link.user.mail' : ($this->action == 'group' ? 'wcf.acp.menu.link.group.mail' : 'wcf.acp.menu.link.user.management'));
	}
	
	/**
	 * @see	\wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['userIDs'])) $this->userIDs = ArrayUtil::toIntegerArray(explode(',', $_POST['userIDs']));
		if (isset($_POST['groupIDs']) && is_array($_POST['groupIDs'])) $this->groupIDs = ArrayUtil::toIntegerArray($_POST['groupIDs']);
		if (isset($_POST['subject'])) $this->subject = StringUtil::trim($_POST['subject']);
		if (isset($_POST['text'])) $this->text = StringUtil::trim($_POST['text']);
		if (isset($_POST['from'])) $this->from = StringUtil::trim($_POST['from']);
		if (isset($_POST['enableHTML'])) $this->enableHTML = true;
	}
	
	/**
	 * @see	\wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
		if ($this->action == 'group' && empty($this->groupIDs)) {
			throw new UserInputException('groupIDs');
		}
		if ($this->action == '' && empty($this->userIDs)) {
			throw new IllegalLinkException();
		}
		
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
	
	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		// save config in session
		$userMailData = WCF::getSession()->getVar('userMailData');
		if ($userMailData === null) $userMailData = array();
		$mailID = count($userMailData);
		$userMailData[$mailID] = array(
			'action' => $this->action,
			'userIDs' => $this->userIDs,
			'groupIDs' => implode(',', $this->groupIDs),
			'subject' => $this->subject,
			'text' => $this->text,
			'from' => $this->from,
			'enableHTML' => $this->enableHTML
		);
		WCF::getSession()->register('userMailData', $userMailData);
		$this->saved();
		
		WCF::getTPL()->assign('mailID', $mailID);
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function readData() {
		parent::readData();
		
		if (empty($_POST)) {
			// get marked user ids
			if (empty($this->action)) {
				// get type id
				$objectTypeID = ClipboardHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.user');
				if ($objectTypeID === null) {
					throw new SystemException("Unknown clipboard item type 'com.woltlab.wcf.user'");
				}
				
				// get user ids
				$users = ClipboardHandler::getInstance()->getMarkedItems($objectTypeID);
				if (empty($users)) {
					throw new IllegalLinkException();
				}
				
				// load users
				$this->userIDs = array_keys($users);
			}
			
			if (MAIL_USE_FORMATTED_ADDRESS) {
				$this->from = MAIL_FROM_NAME.' <'.MAIL_FROM_ADDRESS.'>';
			}
			else {
				$this->from = MAIL_FROM_ADDRESS;
			}
		}
		
		if (!empty($this->userIDs)) {
			$this->userList = new UserList();
			$this->userList->getConditionBuilder()->add("user_table.userID IN (?)", array($this->userIDs));
			$this->userList->sqlOrderBy = "user_table.username ASC";
			$this->userList->readObjects();
		}
		
		$this->groups = UserGroup::getAccessibleGroups(array(), array(UserGroup::GUESTS, UserGroup::EVERYONE));
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'enableHTML' => $this->enableHTML,
			'from' => $this->from,
			'groupIDs' => $this->groupIDs,
			'groups' => $this->groups,
			'subject' => $this->subject,
			'text' => $this->text,
			'userIDs' => $this->userIDs,
			'userList' => $this->userList
		));
	}
}
