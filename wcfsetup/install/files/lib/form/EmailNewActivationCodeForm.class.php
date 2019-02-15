<?php
namespace wcf\form;
use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\data\user\UserList;
use wcf\system\email\mime\MimePartFacade;
use wcf\system\email\mime\RecipientAwareTextMimePart;
use wcf\system\email\Email;
use wcf\system\email\UserMailbox;
use wcf\system\exception\UserInputException;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\UserRegistrationUtil;

/**
 * Shows the new email activation code form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Form
 */
class EmailNewActivationCodeForm extends RegisterNewActivationCodeForm {
	/**
	 * @inheritDoc
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
	 * @inheritDoc
	 */
	public function save() {
		AbstractForm::save();
		
		// generate activation code
		$activationCode = UserRegistrationUtil::getActivationCode();
		
		// save user
		$this->objectAction = new UserAction([$this->user], 'update', [
			'data' => array_merge($this->additionalFields, [
				'reactivationCode' => $activationCode
			])
		]);
		$this->objectAction->executeAction();
		
		// use user list to allow overriding of the fields without duplicating logic
		$userList = new UserList();
		$userList->useQualifiedShorthand = false;
		$userList->sqlSelects .= ", user_table.*, newEmail AS email";
		$userList->getConditionBuilder()->add('user_table.userID = ?', [$this->user->userID]);
		$userList->readObjects();
		$this->user = $userList->getObjects()[$this->user->userID];
		
		// send activation mail
		$email = new Email();
		$email->addRecipient(new UserMailbox($this->user));
		$email->setSubject($this->user->getLanguage()->getDynamicVariable('wcf.user.changeEmail.needReactivation.mail.subject'));
		$email->setBody(new MimePartFacade([
			new RecipientAwareTextMimePart('text/html', 'email_changeEmailNeedReactivation'),
			new RecipientAwareTextMimePart('text/plain', 'email_changeEmailNeedReactivation')
		]));
		$email->send();
		
		$this->saved();
		
		// forward to index page
		HeaderUtil::delayedRedirect(LinkHandler::getInstance()->getLink(), WCF::getLanguage()->getDynamicVariable('wcf.user.changeEmail.needReactivation'), 10);
		exit;
	}
}
