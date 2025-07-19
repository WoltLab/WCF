<?php

namespace wcf\form;

use wcf\data\contact\option\ContactOption;
use wcf\data\contact\option\ContactOptionList;
use wcf\data\contact\recipient\ContactRecipient;
use wcf\data\contact\recipient\ContactRecipientList;
use wcf\event\page\ContactFormSpamChecking;
use wcf\system\contact\form\SubmitContactForm;
use wcf\system\event\EventHandler;
use wcf\system\exception\NamedUserException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\flood\FloodControl;
use wcf\system\form\builder\field\CaptchaFormField;
use wcf\system\form\builder\field\EmailFormField;
use wcf\system\form\builder\field\FileProcessorFormField;
use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\field\SelectFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\HtmlString;
use wcf\util\UserUtil;

/**
 * Customizable contact form with selectable recipients.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractFormBuilderForm<null>
 */
class ContactForm extends AbstractFormBuilderForm
{
    private const ALLOWED_MAILS_PER_10M = 2;

    /**
     * @inheritDoc
     */
    public $neededModules = ['MODULE_CONTACT_FORM'];

    #[\Override]
    protected function createForm()
    {
        parent::createForm();

        $this->form->appendChildren([
            TextFormField::create('name')
                ->label('wcf.contact.sender')
                ->required()
                ->value(WCF::getUser()->username ?: ''),
            EmailFormField::create('email')
                ->label('wcf.user.email')
                ->required()
                ->value(WCF::getUser()->email ?: ''),
            $this->getRecipientFormField(),
            ...$this->getOptionFormFields()
        ]);

        if (\CONTACT_FORM_ENABLE_ATTACHMENTS) {
            $this->form->appendChild($this->getFileUploadFormField());
        }

        if (!WCF::getUser()->userID) {
            $this->form->appendChild(
                CaptchaFormField::create()
                    ->objectType(\CAPTCHA_TYPE)
            );
        }
    }

    #[\Override]
    public function validate()
    {
        $requests = FloodControl::getInstance()->countContent(
            'com.woltlab.wcf.contactForm',
            new \DateInterval('PT10M')
        );
        if ($requests['count'] >= self::ALLOWED_MAILS_PER_10M) {
            throw new NamedUserException(HtmlString::fromSafeHtml(
                WCF::getLanguage()->getDynamicVariable('wcf.page.error.flood')
            ));
        }

        parent::validate();
    }

    #[\Override]
    public function save()
    {
        AbstractForm::save();

        $formData = $this->form->getData();
        $data = $formData['data'];

        $availableRecipients = $this->getAvailableRecipients();
        if (\count($availableRecipients) > 1) {
            $recipient = $availableRecipients[$data['recipientID']];
        } else {
            $recipient = \reset($availableRecipients);
        }

        $optionValues = [];
        foreach ($data as $key => $value) {
            if (\str_starts_with($key, 'option')) {
                $optionValues[\substr($key, 6)] = $value;
            }
        }

        $this->handleSpamCheck(
            $data['email'],
            $optionValues
        );

        $command = new SubmitContactForm(
            $recipient,
            $data['name'],
            $data['email'],
            $optionValues,
            $formData['attachments'] ?? []
        );
        $command();

        $this->saved();

        FloodControl::getInstance()->registerContent('com.woltlab.wcf.contactForm');

        HeaderUtil::delayedRedirect(
            LinkHandler::getInstance()->getLink(),
            WCF::getLanguage()->getDynamicVariable('wcf.contact.success')
        );

        exit;
    }

    /**
     * @param array<int, mixed> $optionValues
     */
    private function handleSpamCheck(string $email, array $optionValues): void
    {
        $messages = [];
        foreach ($this->getAvailableOptions() as $option) {
            if (empty($optionValues[$option->optionID])) {
                continue;
            }

            if (!\in_array($option->optionType, [
                'text',
                'textarea'
            ])) {
                continue;
            }

            $messages[] = $optionValues[$option->optionID];
        }

        $spamCheckEvent = new ContactFormSpamChecking(
            $email,
            UserUtil::getIpAddress(),
            $messages,
        );
        EventHandler::getInstance()->fire($spamCheckEvent);
        if ($spamCheckEvent->defaultPrevented()) {
            throw new PermissionDeniedException();
        }
    }

    protected function getRecipientFormField(): SelectFormField
    {
        $recipients = $this->getAvailableRecipients();
        if ($recipients === []) {
            throw new \BadMethodCallException('Contact form has no available recipients.');
        }

        return SelectFormField::create('recipientID')
            ->label('wcf.contact.recipientID')
            ->required()
            ->options($recipients)
            ->available(\count($recipients) > 1);
    }

    /**
     * @return array<int, ContactRecipient>
     */
    protected function getAvailableRecipients(): array
    {
        $recipientList = new ContactRecipientList();
        $recipientList->getConditionBuilder()->add("contact_recipient.isDisabled = ?", [0]);
        $recipientList->readObjects();

        return $recipientList->getObjects();
    }

    /**
     * @return array<int, ContactOption>
     */
    protected function getAvailableOptions(): array
    {
        $optionList = new ContactOptionList();
        $optionList->getConditionBuilder()->add("contact_option.isDisabled = ?", [0]);
        $optionList->readObjects();

        return $optionList->getObjects();
    }

    /**
     * @return IFormField[]
     */
    protected function getOptionFormFields(): array
    {
        $formFields = [];

        foreach ($this->getAvailableOptions() as $option) {
            $formField = $option->getFormOption()->getFormField(
                'option' . $option->optionID,
                $option->getConfiguration()
            );
            $formField->label($option->optionTitle);
            $formField->description($option->optionDescription);

            if (!empty($option->getConfiguration()['required'])) {
                $formField->required();
            }

            $formFields[] = $formField;
        }

        return $formFields;
    }

    protected function getFileUploadFormField(): FileProcessorFormField
    {
        return FileProcessorFormField::create('attachments')
            ->objectType('com.woltlab.wcf.contact.form')
            ->label('wcf.contact.attachments')
            ->description('wcf.upload.multiple.limits', [
                'maximumCount' => WCF::getSession()->getPermission('user.contactForm.attachment.maxCount'),
                'maximumSize' => WCF::getSession()->getPermission('user.contactForm.attachment.maxSize'),
                'allowedFileExtensions' => \explode(
                    "\n",
                    WCF::getSession()->getPermission('user.contactForm.attachment.allowedExtensions')
                ),
            ]);
    }
}
