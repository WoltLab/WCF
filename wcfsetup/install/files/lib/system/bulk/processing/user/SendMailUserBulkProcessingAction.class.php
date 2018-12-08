<?php
namespace wcf\system\bulk\processing\user;
use wcf\data\user\UserList;
use wcf\data\DatabaseObjectList;
use wcf\system\email\EmailGrammar;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Bulk processing action implementation for sending mails to users.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Bulk\Processing\User
 * @since	3.0
 */
class SendMailUserBulkProcessingAction extends AbstractUserBulkProcessingAction {
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
	 * sender name
	 * @var	string
	 */
	public $fromName = '';
	
	/**
	 * identifier for the mail worker
	 * @var	string
	 */
	public $mailID = '';
	
	/**
	 * email subject
	 * @var	string
	 */
	public $subject = '';
	
	/**
	 * email text
	 * @var	string
	 */
	public $text = '';
	
	/**
	 * @inheritDoc
	 */
	public function executeAction(DatabaseObjectList $objectList) {
		if (!($objectList instanceof UserList)) {
			throw new \InvalidArgumentException("Object list is no instance of '".UserList::class."', instance of '".get_class($objectList)."' given.");
		}
		
		if (count($objectList)) {
			// save config in session
			$userMailData = WCF::getSession()->getVar('userMailData');
			if ($userMailData === null) $userMailData = [];
			$this->mailID = count($userMailData) + 1;
			$userMailData[$this->mailID] = [
				'action' => '',
				'enableHTML' => $this->enableHTML,
				'from' => $this->from,
				'fromName' => $this->fromName,
				'groupIDs' => '',
				'subject' => $this->subject,
				'text' => $this->text,
				'userIDs' => $objectList->getObjectIDs()
			];
			WCF::getSession()->register('userMailData', $userMailData);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function getHTML() {
		if (!count($_POST)) {
			$this->from = MAIL_FROM_ADDRESS;
			$this->fromName = MAIL_FROM_NAME;
		}
		
		return WCF::getTPL()->fetch('sendMailUserBulkProcessing', 'wcf', [
			'enableHTML' => $this->enableHTML,
			'from' => $this->from,
			'mailID' => $this->mailID,
			'subject' => $this->subject,
			'text' => $this->text,
			'fromName' => $this->fromName
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		if (isset($_POST['enableHTML'])) $this->enableHTML = intval($_POST['enableHTML']);
		if (isset($_POST['from'])) $this->from = StringUtil::trim($_POST['from']);
		if (isset($_POST['subject'])) $this->subject = StringUtil::trim($_POST['subject']);
		if (isset($_POST['text'])) $this->text = StringUtil::trim($_POST['text']);
		if (isset($_POST['fromName'])) $this->fromName = StringUtil::trim($_POST['fromName']);
	}
	
	/**
	 * @inheritDoc
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
		else if (!preg_match('(^'.EmailGrammar::getGrammar('addr-spec').'$)', $this->from)) {
			throw new UserInputException('from', 'invalid');
		}
	}
}
