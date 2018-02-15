<?php
namespace wcf\form;
use wcf\data\contact\option\ContactOptionAction;
use wcf\data\contact\recipient\ContactRecipientList;
use wcf\system\email\Mailbox;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\UserInputException;
use wcf\system\option\ContactOptionHandler;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;

/**
 * Customizable contact form with selectable recipients.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Form
 */
class ContactForm extends AbstractCaptchaForm {
	/**
	 * sender email address
	 * @var string
	 */
	public $email = '';
	
	/**
	 * sender name
	 * @var string
	 */
	public $name = '';
	
	/**
	 * privacy policy consent acceptance
	 * @var string
	 */
	public $privacyPolicyConsent = 0;
	
	/**
	 * @inheritDoc
	 */
	public $neededModules = ['MODULE_CONTACT_FORM'];
	
	/**
	 * @var ContactOptionHandler
	 */
	public $optionHandler;
	
	/**
	 * recipient id
	 * @var integer
	 */
	public $recipientID = 0;
	
	/**
	 * @var ContactRecipientList
	 */
	public $recipientList;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->optionHandler = new ContactOptionHandler(false);
		$this->optionHandler->init();
		
		$this->recipientList = new ContactRecipientList();
		$this->recipientList->getConditionBuilder()->add("contact_recipient.isDisabled = ?", [0]);
		$this->recipientList->readObjects();
		
		if (count($this->recipientList) < 0) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		$this->optionHandler->readUserInput($_POST);
		
		if (isset($_POST['email'])) $this->email = StringUtil::trim($_POST['email']);
		if (isset($_POST['name'])) $this->name = StringUtil::trim($_POST['name']);
		if (isset($_POST['privacyPolicyConsent'])) $this->privacyPolicyConsent = 1;
		if (isset($_POST['recipientID'])) $this->recipientID = intval($_POST['recipientID']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		// validate file options
		$optionHandlerErrors = $this->optionHandler->validate();
		
		parent::validate();
		
		if (!empty($optionHandlerErrors)) {
			throw new UserInputException('options', $optionHandlerErrors);
		}
		
		if (empty($this->email)) {
			throw new UserInputException('email');
		}
		else {
			try {
				new Mailbox($this->email);
			}
			catch (\DomainException $e) {
				throw new UserInputException('email', 'invalid');
			}
		}
		
		if (empty($this->name)) {
			throw new UserInputException('name');
		}
		
		if (empty($this->privacyPolicyConsent)) {
			throw new UserInputException('privacyPolicyConsent');
		}
		
		$recipients = $this->recipientList->getObjects();
		if (count($recipients) === 1) {
			$this->recipientID = reset($recipients)->recipientID;
		}
		else {
			if (!$this->recipientID) {
				throw new UserInputException('recipientID');
			}
			
			$isValid = false;
			foreach ($recipients as $recipient) {
				if ($this->recipientID == $recipient->recipientID) {
					$isValid = true;
					break;
				}
			}
			
			if (!$isValid) {
				throw new UserInputException('recipientID', 'invalid');
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		if (empty($_POST)) {
			if (WCF::getUser()->userID) {
				$this->email = WCF::getUser()->email;
				$this->name = WCF::getUser()->username;
			}
			
			$this->optionHandler->readData();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		$this->objectAction = new ContactOptionAction([], 'send', [
			'email' => $this->email,
			'name' => $this->name,
			'optionHandler' => $this->optionHandler,
			'recipientID' => $this->recipientID
		]);
		$this->objectAction->executeAction();
		
		// call saved event
		$this->saved();
		
		HeaderUtil::delayedRedirect(
			LinkHandler::getInstance()->getLink(''),
			WCF::getLanguage()->get('wcf.contact.success')
		);
		exit;
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'email' => $this->email,
			'name' => $this->name,
			'options' => $this->optionHandler->getOptions(),
			'recipientList' => $this->recipientList
		]);
	}
}
