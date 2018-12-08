<?php
namespace wcf\acp\form;
use wcf\data\contact\recipient\ContactRecipient;
use wcf\data\contact\recipient\ContactRecipientAction;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\language\I18nHandler;
use wcf\system\WCF;

/**
 * Shows the form to update a contact form recipient.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 */
class ContactRecipientEditForm extends ContactRecipientAddForm {
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
	 * @var ContactRecipient
	 */
	public $recipient;
	
	/**
	 * @var integer
	 */
	public $recipientID = 0;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->recipientID = intval($_REQUEST['id']);
		$this->recipient = new ContactRecipient($this->recipientID);
		if (!$this->recipient->recipientID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		parent::validate();
		
		if ($this->recipient->isAdministrator) {
			$this->isDisabled = 0;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		if (empty($_POST)) {
			I18nHandler::getInstance()->setOptions('name', 1, $this->recipient->name, 'wcf.contact.recipient.name\d+');
			I18nHandler::getInstance()->setOptions('email', 1, $this->recipient->email, 'wcf.contact.recipient.email\d+');
			
			$this->name = $this->recipient->name;
			$this->email = $this->recipient->email;
			$this->isDisabled = $this->recipient->isDisabled;
			$this->showOrder = $this->recipient->showOrder;
			
			if ($this->recipient->isAdministrator) {
				$this->email = MAIL_ADMIN_ADDRESS;
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		AbstractForm::save();
		
		$this->name = 'wcf.contact.recipient.name'.$this->recipient->recipientID;
		if (I18nHandler::getInstance()->isPlainValue('name')) {
			I18nHandler::getInstance()->remove($this->name);
			$this->name = I18nHandler::getInstance()->getValue('name');
		}
		else {
			I18nHandler::getInstance()->save('name', $this->name, 'wcf.contact', 1);
		}
		$this->email = 'wcf.contact.recipient.email'.$this->recipient->recipientID;
		if (!$this->recipient->isAdministrator) {
			if (I18nHandler::getInstance()->isPlainValue('email')) {
				I18nHandler::getInstance()->remove($this->email);
				$this->email = I18nHandler::getInstance()->getValue('email');
			}
			else {
				I18nHandler::getInstance()->save('email', $this->email, 'wcf.contact', 1);
			}
		}
		
		$data = [
			'name' => $this->name,
			'isDisabled' => $this->isDisabled ? 1 : 0,
			'showOrder' => $this->showOrder
		];
		if (!$this->recipient->isAdministrator) {
			$data['email'] = $this->email;
		}
		
		$this->objectAction = new ContactRecipientAction([$this->recipient], 'update', [
			'data' => array_merge($this->additionalFields, $data)
		]);
		$this->objectAction->executeAction();
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		I18nHandler::getInstance()->assignVariables(!empty($_POST));
		
		WCF::getTPL()->assign([
			'recipientID' => $this->recipientID,
			'recipient' => $this->recipient,
			'action' => 'edit'
		]);
	}
}
