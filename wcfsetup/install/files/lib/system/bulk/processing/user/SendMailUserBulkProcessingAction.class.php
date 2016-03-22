<?php
namespace wcf\system\bulk\processing\user;
use wcf\data\user\UserList;
use wcf\data\DatabaseObjectList;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Bulk processing action implementation for sening mails to users.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.bulk.processing.user
 * @category	Community Framework
 * @since	2.2
 */
class SendMailUserBulkProcessingAction extends AbstractUserBulkProcessingAction {
	/**
	 * email text
	 * @var	string
	 */
	public $email = '';
	
	/**
	 * is 1 if HTML for the email is enabled
	 * @var	integer
	 */
	public $enableHTML = 0;
	
	/**
	 * sender
	 * @var	string
	 */
	public $from = '';
	
	/**
	 * identifier for the mail worker
	 * @var	string
	 */
	public $mailID = '';
	
	/**
	 * email text
	 * @var	string
	 */
	public $text = '';
	
	/**
	 * @see	\wcf\system\bulk\processing\IBulkProcessingAction::executeAction()
	 */
	public function executeAction(DatabaseObjectList $objectList) {
		if (!($objectList instanceof UserList)) return;
		
		if (count($objectList)) {
			// save config in session
			$userMailData = WCF::getSession()->getVar('userMailData');
			if ($userMailData === null) $userMailData = array();
			$this->mailID = count($userMailData);
			$userMailData[$this->mailID] = array(
				'action' => '',
				'enableHTML' => $this->enableHTML,
				'from' => $this->from,
				'groupIDs' => '',
				'subject' => $this->subject,
				'text' => $this->text,
				'userIDs' => $objectList->getObjectIDs()
			);
			WCF::getSession()->register('userMailData', $userMailData);
		}
	}
	
	/**
	 * @see	\wcf\system\bulk\processing\IBulkProcessingAction::getHTML()
	 */
	public function getHTML() {
		return WCF::getTPL()->fetch('sendMailUserBulkProcessing', 'wcf', [
			'enableHTML' => $this->enableHTML,
			'from' => $this->from,
			'mailID' => $this->mailID,
			'subject' => $this->subject,
			'text' => $this->text
		]);
	}
	
	/**
	 * @see	\wcf\system\bulk\processing\IBulkProcessingAction::readFormParameters()
	 */
	public function readFormParameters() {
		if (isset($_POST['enableHTML'])) $this->enableHTML = intval($_POST['enableHTML']);
		if (isset($_POST['from'])) $this->from = StringUtil::trim($_POST['from']);
		if (isset($_POST['subject'])) $this->subject = StringUtil::trim($_POST['subject']);
		if (isset($_POST['text'])) $this->text = StringUtil::trim($_POST['text']);
	}
	
	/**
	 * @see	\wcf\system\bulk\processing\IBulkProcessingAction::validate()
	 */
	public function validate() {
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
