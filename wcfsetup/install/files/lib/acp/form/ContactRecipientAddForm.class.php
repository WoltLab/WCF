<?php
namespace wcf\acp\form;
use wcf\data\contact\recipient\ContactRecipient;
use wcf\data\contact\recipient\ContactRecipientAction;
use wcf\data\contact\recipient\ContactRecipientEditor;
use wcf\form\AbstractForm;
use wcf\system\email\Mailbox;
use wcf\system\exception\UserInputException;
use wcf\system\language\I18nHandler;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows the form to create a new contact form recipient.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 */
class ContactRecipientAddForm extends AbstractForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.contact.settings';
	
	/**
	 * @inheritDoc
	 */
	public $neededModules = ['MODULE_CONTACT_FORM'];
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.contact.canManageContactForm'];
	
	/**
	 * email address
	 * @var string
	 */
	public $email = '';
	
	/**
	 * display name
	 * @var string
	 */
	public $name = '';
	
	/**
	 * 1 if the recipient is disabled
	 * @var	integer
	 */
	public $isDisabled = 0;
	
	/**
	 * order used to the show the recipients
	 * @var	integer
	 */
	public $showOrder = 0;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		I18nHandler::getInstance()->register('email');
		I18nHandler::getInstance()->register('name');
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		I18nHandler::getInstance()->readValues();
		
		if (I18nHandler::getInstance()->isPlainValue('email')) $this->email = I18nHandler::getInstance()->getValue('email');
		if (I18nHandler::getInstance()->isPlainValue('name')) $this->name = I18nHandler::getInstance()->getValue('name');
		
		if (isset($_POST['isDisabled'])) $this->isDisabled = intval($_POST['isDisabled']);
		if (isset($_POST['showOrder'])) $this->showOrder = intval($_POST['showOrder']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		parent::validate();
		
		if (!I18nHandler::getInstance()->validateValue('email')) {
			if (I18nHandler::getInstance()->isPlainValue('email')) {
				throw new UserInputException('email');
			}
			else {
				throw new UserInputException('email', 'multilingual');
			}
		}
		else {
			foreach (I18nHandler::getInstance()->getValues('email') as $email) {
				try {
					new Mailbox($email);
				}
				catch (\DomainException $e) {
					throw new UserInputException('email', 'invalid');
				}
			}
		}
		
		if (!I18nHandler::getInstance()->validateValue('name')) {
			if (I18nHandler::getInstance()->isPlainValue('name')) {
				throw new UserInputException('name');
			}
			else {
				throw new UserInputException('name', 'multilingual');
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		$this->objectAction = new ContactRecipientAction([], 'create', [
			'data' => array_merge($this->additionalFields, [
				'name' => $this->name,
				'email' => $this->email,
				'isDisabled' => ($this->isDisabled ? 1 : 0),
				'showOrder' => $this->showOrder
			])
		]);
		/** @var ContactRecipient $recipient */
		$recipient = $this->objectAction->executeAction()['returnValues'];
		$recipientID = $recipient->recipientID;
		$data = [];
		
		if (!I18nHandler::getInstance()->isPlainValue('email')) {
			I18nHandler::getInstance()->save('email', 'wcf.contact.recipient.email'.$recipientID, 'wcf.contact', 1);
			
			$data['email'] = 'wcf.contact.recipient.email'.$recipientID;
		}
		if (!I18nHandler::getInstance()->isPlainValue('name')) {
			I18nHandler::getInstance()->save('name', 'wcf.contact.recipient.name'.$recipientID, 'wcf.contact', 1);
			
			$data['name'] = 'wcf.contact.recipient.name'.$recipientID;
		}
		
		// update i18n values
		if (!empty($data)) {
			(new ContactRecipientEditor($recipient))->update($data);
		}
		
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign([
			'success' => true,
			'objectEditLink' => LinkHandler::getInstance()->getLink('ContactRecipientEdit', ['id' => $recipientID]),
		]);
		
		// reset values
		$this->email = $this->name = 0;
		$this->isDisabled = $this->showOrder = 0;
		
		I18nHandler::getInstance()->reset();
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		I18nHandler::getInstance()->assignVariables();
		
		WCF::getTPL()->assign([
			'action' => 'add',
			'email' => $this->email,
			'name' => $this->name,
			'isDisabled' => $this->isDisabled,
			'showOrder' => $this->showOrder
		]);
	}
}
