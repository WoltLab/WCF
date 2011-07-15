<?php
namespace wcf\acp\form;
use wcf\data\user\User;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;

/**
 * Shows the export user mail addresses form.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category 	Community Framework
 */
class UserEmailAddressExportForm extends ACPForm {
	public $templateName = 'userEmailAddressExport';
	public $activeMenuItem = 'wcf.acp.menu.link.user.management';
	public $neededPermissions = array('admin.user.canMailUser');
	
	public $fileType = 'csv';
	public $userIDs = array();
	public $separator = ',';
	public $textSeparator = '"'; 
	public $users = array();
	
	/**
	 * @see wcf\form\Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['fileType']) && $_POST['fileType'] == 'xml') $this->fileType = $_POST['fileType'];
		if (isset($_POST['userIDs'])) $this->userIDs = ArrayUtil::toIntegerArray(explode(',', $_POST['userIDs']));
		if (isset($_POST['separator'])) $this->separator = $_POST['separator'];
		if (isset($_POST['textSeparator'])) $this->textSeparator = $_POST['textSeparator'];
	}
	
	/**
	 * @see wcf\form\Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		if (empty($this->userIDs)) throw new IllegalLinkException();
	}
	
	/**
	 * @see wcf\form\Form::save()
	 */
	public function save() {
		parent::save();
		
		// send content type
		header('Content-Type: text/'.$this->fileType.'; charset=UTF-8');
		header('Content-Disposition: attachment; filename="export.'.$this->fileType.'"');
		
		if ($this->fileType == 'xml') {
			echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<addresses>\n";
		}
		
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("userID IN (?)", array($this->userIDs));
		
		// count users
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_user
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		$count = $statement->fetchArray();
		
		// get users
		$sql = "SELECT		email
			FROM		wcf".WCF_N."_user
			".$conditions."
			ORDER BY	email";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		
		$i = 0;
		while ($row = WCF::getDB()->fetchArray($result)) {
			if ($this->fileType == 'xml') echo "<address><![CDATA[".StringUtil::escapeCDATA($row['email'])."]]></address>\n";
			else echo $this->textSeparator . $row['email'] . $this->textSeparator . ($i < $count['count'] ? $this->separator : '');
			$i++;
		}
		
		if ($this->fileType == 'xml') {
			echo "</addresses>";
		}
		
		// TODO: Implement unmarkAll()
		//UserEditor::unmarkAll();
		$this->saved();
		exit;
	}
	
	/**
	 * @see wcf\page\Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		if (!count($_POST)) {
			// get marked user ids
			$markedUsers = WCF::getSession()->getVar('markedUsers');
			if (is_array($markedUsers)) $this->userIDs = implode(',', $markedUsers);
			if (empty($this->userIDs)) throw new IllegalLinkException();
		}
		
		$this->users = User::getUsers($this->userIDs);
	}
	
	/**
	 * @see wcf\page\Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'users' => $this->users,
			'userIDs' => $this->userIDs,
			'separator' => $this->separator,
			'textSeparator' => $this->textSeparator,
			'fileType' => $this->fileType
		));
	}
}
