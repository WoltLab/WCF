<?php
namespace wcf\form;
use wcf\data\user\User;
use wcf\data\user\UserEditor;
use wcf\system\exception\UserInputException;
use wcf\system\mail\Mail;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\UserRegistrationUtil;

/**
 * Shows the new email activation code form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	form
 * @category	Community Framework
 */
class EmailNewActivationCodeForm extends RegisterNewActivationCodeForm {
	/**
	 * @see	wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (WCF::getUser()->userID) {
			$this->username = WCF::getUser()->username;
		}
	}
	
	/**
	 * Validates the username.
	 */
	public function validateUsername() {
		if (empty($this->username)) {
			throw new UserInputException('username');
		}
		
		$this->user = User::getUserByUsername($this->username);
		if (!$this->user->userID) {
			throw new UserInputException('username', 'notFound');
		}
		
		if ($this->user->reactivationCode == 0) {
			throw new UserInputException('username', 'alreadyEnabled');
		}
	}
	
	/**
	 * @see	wcf\form\IForm::save()
	 */
	public function save() {
		AbstractForm::save();
		
		// generate activation code
		$activationCode = UserRegistrationUtil::getActivationCode();
		
		// save user
		$userEditor = new UserEditor($this->user);
		$userEditor->update(array('reactivationCode' => $activationCode));
		
		// send activation mail
		$messageData = array(
			'username' => $this->user->username,
			'userID' => $this->user->userID,
			'activationCode' => $activationCode
		);
		$mail = new Mail(array($this->user->username => !empty($this->email) ? $this->email : $this->user->email), WCF::getLanguage()->getDynamicVariable('wcf.user.changeEmail.needReactivation.mail.subject'), WCF::getLanguage()->getDynamicVariable('wcf.user.changeEmail.needReactivation.mail', $messageData));
		$mail->send();
		$this->saved();
		
		// forward to index page
		HeaderUtil::delayedRedirect(LinkHandler::getInstance()->getLink(), WCF::getLanguage()->getDynamicVariable('wcf.user.changeEmail.needReactivation'), 10);
		exit;
	}
}
