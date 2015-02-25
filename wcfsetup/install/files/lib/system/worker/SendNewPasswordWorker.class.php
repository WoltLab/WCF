<?php
namespace wcf\system\worker;
use wcf\data\user\UserAction;
use wcf\data\user\UserEditor;
use wcf\data\user\UserList;
use wcf\system\clipboard\ClipboardHandler;
use wcf\system\exception\SystemException;
use wcf\system\mail\Mail;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\PasswordUtil;

/**
 * Worker implementation for sending new passwords.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.worker
 * @category	Community Framework
 */
class SendNewPasswordWorker extends AbstractWorker {
	/**
	 * @see	\wcf\system\worker\AbstractWorker::$limit
	 */
	protected $limit = 50;
	
	/**
	 * @see	\wcf\system\worker\IWorker::countObjects()
	 */
	public function countObjects() {
		$userList = new UserList();
		$userList->getConditionBuilder()->add('user_table.userID IN (?)', array($this->parameters['userIDs']));
		
		return $userList->countObjects();
	}
	
	/**
	 * @see	\wcf\system\worker\IWorker::execute()
	 */
	public function execute() {
		$userList = new UserList();
		$userList->decoratorClassName = 'wcf\data\user\UserEditor';
		$userList->getConditionBuilder()->add('user_table.userID IN (?)', array($this->parameters['userIDs']));
		$userList->sqlLimit = $this->limit;
		$userList->sqlOffset = $this->limit * $this->loopCount;
		$userList->readObjects();
		
		foreach ($userList as $userEditor) {
			$this->sendNewPassword($userEditor);
		}
	}
	
	/**
	 * @see	\wcf\system\worker\IWorker::getProceedURL()
	 */
	public function getProceedURL() {
		return LinkHandler::getInstance()->getLink('UserList');
	}
	
	/**
	 * @see	\wcf\system\worker\IWorker::getProgress()
	 */
	public function getProgress() {
		$progress = parent::getProgress();
		
		if ($progress == 100) {
			// unmark users
			ClipboardHandler::getInstance()->unmark($this->parameters['userIDs'], ClipboardHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.user'));
		}
		
		return $progress;
	}
	
	/**
	 * Sends a new password to the given user.
	 * 
	 * @param	\wcf\data\user\UserEditor	$userEditor
	 */
	protected function sendNewPassword(UserEditor $userEditor) {
		$newPassword = PasswordUtil::getRandomPassword((REGISTER_PASSWORD_MIN_LENGTH > 12 ? REGISTER_PASSWORD_MIN_LENGTH : 12));
		
		$userAction = new UserAction(array($userEditor), 'update', array(
			'data' => array(
				'password' => $newPassword
			)
		));
		$userAction->executeAction();
		
		// send mail
		$mail = new Mail(array($userEditor->username => $userEditor->email), $userEditor->getLanguage()->getDynamicVariable('wcf.acp.user.sendNewPassword.mail.subject'), $userEditor->getLanguage()->getDynamicVariable('wcf.acp.user.sendNewPassword.mail', array(
			'password' => $newPassword,
			'username' => $userEditor->username,
		)));
		$mail->send();
	}
	
	/**
	 * @see	\wcf\system\worker\IWorker::validate()
	 */
	public function validate() {
		WCF::getSession()->checkPermissions(array('admin.user.canEditPassword'));
		
		if (!isset($this->parameters['userIDs']) || !is_array($this->parameters['userIDs']) || empty($this->parameters['userIDs'])) {
			throw new SystemException("'userIDs' parameter is missing or invalid");
		}
	}
}
