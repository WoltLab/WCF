<?php
namespace wcf\acp\form;
use wcf\data\user\group\UserGroup;
use wcf\data\user\UserList;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;
use wcf\system\WCF;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\UserInputException;

/**
 * Shows the user mail form.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category 	Community Framework
 */
class UserMailForm extends ACPForm {
	// system
	public $templateName = 'userMail';
	public $neededPermissions = array('admin.user.canMailUser');
	
	// parameters
	public $userIDs = array();
	public $groupIDs = array();
	public $subject = '';
	public $text = '';
	public $from = '';
	public $users = array();
	public $groups = array();
	public $enableHTML = 0;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->activeMenuItem = ($this->action == 'all' ? 'wcf.acp.menu.link.user.mail' : ($this->action == 'group' ? 'wcf.acp.menu.link.group.mail' : 'wcf.acp.menu.link.user.management'));
	}
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['userIDs'])) $this->userIDs = ArrayUtil::toIntegerArray(explode(',', $_POST['userIDs']));
		if (isset($_POST['groupIDs']) && is_array($_POST['groupIDs'])) $this->groupIDs = ArrayUtil::toIntegerArray($_POST['groupIDs']);
		if (isset($_POST['subject'])) $this->subject = StringUtil::trim($_POST['subject']);
		if (isset($_POST['text'])) $this->text = StringUtil::trim($_POST['text']);
		if (isset($_POST['from'])) $this->from = StringUtil::trim($_POST['from']);
		if (isset($_POST['enableHTML'])) $this->enableHTML = intval($_POST['enableHTML']);
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		if ($this->action == 'group') {
			if (!count($this->groupIDs)) {
				throw new UserInputException('groupIDs');
			}
		}
		if ($this->action == '') {
			if (empty($this->userIDs)) throw new IllegalLinkException();
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
	 * @see Form::save()
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
		
		// show worker template
		WCF::getTPL()->assign(array(
			'pageTitle' => WCF::getLanguage()->get(($this->action == 'all' ? 'wcf.acp.user.sendMail.all' : ($this->action == 'group' ? 'wcf.acp.user.sendMail.group' : 'wcf.acp.user.sendMail'))),
			'url' => 'index.php?action=UserMail&mailID='.$mailID.''.SID_ARG_2ND_NOT_ENCODED
		));
		WCF::getTPL()->display('worker');
		exit;
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function readData() {
		parent::readData();
		
		if (!count($_POST)) {
			// get marked user ids
			if (empty($this->action)) {
				$markedUsers = WCF::getSession()->getVar('markedUsers');
				if (is_array($markedUsers)) $this->userIDs = implode(',', $markedUsers);
				if (empty($this->userIDs)) throw new IllegalLinkException();
			}
			
			if (MAIL_USE_FORMATTED_ADDRESS)	$this->from = MAIL_FROM_NAME . ' <' . MAIL_FROM_ADDRESS . '>';
			else $this->from = MAIL_FROM_ADDRESS;
		}
		
		if (!empty($this->userIDs)) {
			$userList = new UserList();
			$userList->getConditionBuilder()->add("user.userID IN (?)", array($this->userIDs));
			$userList->sqlOrderBy = "user.username ASC";
			$userList->readObjects();
			
			$this->users = $userList->getObjects();
		}
		
		$this->groups = UserGroup::getAccessibleGroups(array(), array(UserGroup::GUESTS, UserGroup::EVERYONE));
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'users' => $this->users,
			'groups' => $this->groups,
			'userIDs' => $this->userIDs,
			'groupIDs' => $this->groupIDs,
			'subject' => $this->subject,
			'text' => $this->text,
			'from' => $this->from,
			'enableHTML' => $this->enableHTML
		));
	}
}
?>