<?php
namespace wcf\data\contact\option;
use wcf\data\contact\recipient\ContactRecipient;
use wcf\data\custom\option\CustomOptionAction;
use wcf\system\email\mime\MimePartFacade;
use wcf\system\email\mime\RecipientAwareTextMimePart;
use wcf\system\email\Email;
use wcf\system\email\Mailbox;
use wcf\system\language\LanguageFactory;
use wcf\system\option\ContactOptionHandler;

/**
 * Executes contact option related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Contact\Option
 * @since	3.1
 * 
 * @method	ContactOptionEditor[]	getObjects()
 * @method	ContactOptionEditor	getSingleObject()
 */
class ContactOptionAction extends CustomOptionAction {
	/**
	 * @inheritDoc
	 */
	protected $className = ContactOptionEditor::class;
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsCreate = ['admin.contact.canManageContactForm'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsDelete = ['admin.contact.canManageContactForm'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsUpdate = ['admin.contact.canManageContactForm'];
	
	/**
	 * Sends an email to the selected recipient.
	 */
	public function send() {
		$defaultLanguage = LanguageFactory::getInstance()->getDefaultLanguage();
		
		$recipient = new ContactRecipient($this->parameters['recipientID']);
		/** @var ContactOptionHandler $optionHandler */
		$optionHandler = $this->parameters['optionHandler'];
		
		$options = [];
		foreach ($optionHandler->getOptions() as $option) {
			/** @var ContactOption $object */
			$object = $option['object'];
			if ($object->optionType === 'date' && !$object->getOptionValue()) {
				// skip empty dates
				continue;
			}
			
			$options[] = [
				'isMessage' => $object->isMessage(),
				'title' => $object->getLocalizedName($defaultLanguage),
				'value' => $object->getFormattedOptionValue(true),
				'htmlValue' => $object->getFormattedOptionValue(),
			];
		}
		
		// build message data
		$messageData = [
			'options' => $options,
			'recipient' => $recipient,
			'name' => $this->parameters['name'],
			'emailAddress' => $this->parameters['email']
		];
		
		// build mail
		$email = new Email();
		$email->addRecipient(new Mailbox($recipient->email));
		$email->setSubject($defaultLanguage->get('wcf.contact.mail.subject'));
		$email->setBody(new MimePartFacade([
			new RecipientAwareTextMimePart('text/html', 'email_contact', 'wcf', $messageData),
			new RecipientAwareTextMimePart('text/plain', 'email_contact', 'wcf', $messageData)
		]));
		
		// add reply-to tag
		$email->setReplyTo(new Mailbox($this->parameters['email']));
		
		// send mail
		$email->send();
	}
}
