<?php
namespace wcf\system\worker;
use wcf\data\user\User;
use wcf\system\clipboard\ClipboardHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\mail\Mail;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Worker implementation for sending mails.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Worker
 */
class MailWorker extends AbstractWorker {
	/**
	 * condition builder object
	 * @var	\wcf\system\database\util\PreparedStatementConditionBuilder
	 */
	protected $conditions = null;
	
	/**
	 * @inheritDoc
	 */
	protected $limit = 50;
	
	/**
	 * mail data
	 * @var	array
	 */
	protected $mailData = null;
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		WCF::getSession()->checkPermissions(['admin.user.canMailUser']);
		
		if (!isset($this->parameters['mailID'])) {
			throw new SystemException("mailID missing");
		}
		
		$userMailData = WCF::getSession()->getVar('userMailData');
		if (!isset($userMailData[$this->parameters['mailID']])) {
			throw new SystemException("mailID '" . $this->parameters['mailID'] . "' is invalid");
		}
		
		$this->mailData = $userMailData[$this->parameters['mailID']];
	}
	
	/**
	 * @inheritDoc
	 */
	public function countObjects() {
		$this->conditions = new PreparedStatementConditionBuilder();
		if ($this->mailData['action'] == '') {
			$this->conditions->add("user.userID IN (?)", [$this->mailData['userIDs']]);
		}
		else {
			$this->conditions->add("user.activationCode = ?", [0]);
			$this->conditions->add("user.banned = ?", [0]);
			
			if ($this->mailData['action'] == 'group') {
				$this->conditions->add("user.userID IN (SELECT userID FROM wcf".WCF_N."_user_to_group WHERE groupID IN (?))", [$this->mailData['groupIDs']]);
			}
		}
		
		$sql = "SELECT	COUNT(*)
			FROM	wcf".WCF_N."_user user
			".$this->conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($this->conditions->getParameters());
		
		$this->count = $statement->fetchSingleColumn();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getProgress() {
		$progress = parent::getProgress();
		
		if ($progress == 100) {
			// clear markings
			$typeID = ClipboardHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.user');
			ClipboardHandler::getInstance()->removeItems($typeID);
			
			// clear session
			$userMailData = WCF::getSession()->getVar('userMailData');
			unset($userMailData[$this->parameters['mailID']]);
			WCF::getSession()->register('userMailData', $userMailData);
		}
		
		return $progress;
	}
	
	/**
	 * @inheritDoc
	 */
	public function execute() {
		// get users
		$sql = "SELECT		user_option.*, user.*
			FROM		wcf".WCF_N."_user user
			LEFT JOIN	wcf".WCF_N."_user_option_value user_option
			ON		(user_option.userID = user.userID)
			".$this->conditions."
			ORDER BY	user.userID";
		$statement = WCF::getDB()->prepareStatement($sql, $this->limit, ($this->limit * $this->loopCount));
		$statement->execute($this->conditions->getParameters());
		while ($row = $statement->fetchArray()) {
			$user = new User(null, $row);
			$adminCanMail = $user->adminCanMail;
			if ($adminCanMail === null || $adminCanMail) {
				$this->sendMail($user);
			}
		}
	}
	
	/**
	 * Sends the mail to given user.
	 * 
	 * @param	\wcf\data\user\User	$user
	 */
	protected function sendMail(User $user) {
		try {
			$mail = new Mail([$user->username => $user->email], $this->mailData['subject'], str_replace('{$username}', $user->username, $this->mailData['text']), $this->mailData['from']);
			if ($this->mailData['enableHTML']) $mail->setContentType('text/html');
			$mail->setLanguage($user->getLanguage());
			$mail->send();
		}
		catch (SystemException $e) {
			// ignore errors
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function getProceedURL() {
		return LinkHandler::getInstance()->getLink('UserList');
	}
}
