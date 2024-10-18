<?php

namespace wcf\data\contact\option;

use wcf\data\attachment\AttachmentEditor;
use wcf\data\contact\attachment\ContactAttachment;
use wcf\data\contact\attachment\ContactAttachmentEditor;
use wcf\data\contact\recipient\ContactRecipient;
use wcf\data\custom\option\CustomOptionAction;
use wcf\data\ISortableAction;
use wcf\system\attachment\AttachmentHandler;
use wcf\system\email\Email;
use wcf\system\email\Mailbox;
use wcf\system\email\mime\MimePartFacade;
use wcf\system\email\mime\RecipientAwareTextMimePart;
use wcf\system\exception\UserInputException;
use wcf\system\language\LanguageFactory;
use wcf\system\option\ContactOptionHandler;
use wcf\system\WCF;

/**
 * Executes contact option related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.1
 *
 * @method  ContactOptionEditor[]   getObjects()
 * @method  ContactOptionEditor getSingleObject()
 */
class ContactOptionAction extends CustomOptionAction implements ISortableAction
{
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
     * @inheritDoc
     */
    protected $requireACP = ['create', 'delete', 'update', 'updatePosition'];

    /**
     * Sends an email to the selected recipient.
     */
    public function send()
    {
        $defaultLanguage = LanguageFactory::getInstance()->getDefaultLanguage();

        $recipient = new ContactRecipient($this->parameters['recipientID']);
        /** @var ContactOptionHandler $optionHandler */
        $optionHandler = $this->parameters['optionHandler'];

        /** @var AttachmentHandler $attachmentHandler */
        $attachmentHandler = (!empty($this->parameters['attachmentHandler'])) ? $this->parameters['attachmentHandler'] : null;

        /** @var ContactAttachment[] $attachments */
        $attachments = [];
        if ($attachmentHandler !== null) {
            foreach ($attachmentHandler->getAttachmentList() as $attachment) {
                $attachments[] = ContactAttachmentEditor::create([
                    'attachmentID' => $attachment->attachmentID,
                    'accessKey' => ContactAttachment::generateKey(),
                ]);

                (new AttachmentEditor($attachment))->update([
                    'objectID' => $attachment->attachmentID,
                    'tmpHash' => '',
                ]);
            }
        }

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
            'emailAddress' => $this->parameters['email'],
            'attachments' => $attachments,
        ];

        // build mail
        $email = new Email();
        $email->addRecipient($recipient->getMailbox());
        $email->setSubject($defaultLanguage->get('wcf.contact.mail.subject'));
        $email->setBody(new MimePartFacade([
            new RecipientAwareTextMimePart('text/html', 'email_contact', 'wcf', $messageData),
            new RecipientAwareTextMimePart('text/plain', 'email_contact', 'wcf', $messageData),
        ]));

        // add reply-to tag
        $email->setReplyTo(new Mailbox($this->parameters['email'], $this->parameters['name']));

        // send mail
        $email->send();
    }

    /**
     * @inheritDoc
     */
    public function validateUpdatePosition()
    {
        WCF::getSession()->checkPermissions($this->permissionsUpdate);

        if (!isset($this->parameters['data']['structure']) || !\is_array($this->parameters['data']['structure'])) {
            throw new UserInputException('structure');
        }

        $recipientList = new ContactOptionList();
        $recipientList->setObjectIDs($this->parameters['data']['structure'][0]);
        if ($recipientList->countObjects() != \count($this->parameters['data']['structure'][0])) {
            throw new UserInputException('structure');
        }
    }

    /**
     * @inheritDoc
     */
    public function updatePosition()
    {
        $sql = "UPDATE  wcf1_contact_option
                SET     showOrder = ?
                WHERE   optionID = ?";
        $statement = WCF::getDB()->prepare($sql);

        $showOrder = 1;
        WCF::getDB()->beginTransaction();
        foreach ($this->parameters['data']['structure'][0] as $optionID) {
            $statement->execute([
                $showOrder++,
                $optionID,
            ]);
        }
        WCF::getDB()->commitTransaction();
    }
}
