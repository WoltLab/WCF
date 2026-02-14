<?php

namespace wcf\command\contact\form;

use wcf\data\contact\option\ContactOption;
use wcf\data\contact\option\ContactOptionList;
use wcf\data\contact\recipient\ContactRecipient;
use wcf\data\file\File;
use wcf\data\file\FileList;
use wcf\system\email\Email;
use wcf\system\email\Mailbox;
use wcf\system\email\mime\MimePartFacade;
use wcf\system\email\mime\RecipientAwareTextMimePart;
use wcf\system\language\LanguageFactory;

/**
 * Handles the submit of the contact form.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class SubmitContactForm
{
    /**
     * @param array<int, mixed> $optionValues
     * @param int[] $fileIDs
     */
    public function __construct(
        private readonly ContactRecipient $recipient,
        private readonly string $senderName,
        private readonly string $senderEmail,
        private readonly array $optionValues = [],
        private readonly array $fileIDs = []
    ) {}

    public function __invoke(): void
    {
        $messageData = [
            'options' => $this->getFormattedOptionValues($this->optionValues),
            'recipient' => $this->recipient,
            'name' => $this->senderName,
            'emailAddress' => $this->senderEmail,
            'attachments' => $this->getFiles($this->fileIDs),
        ];

        $email = new Email();
        $email->addRecipient($this->recipient->getMailbox());
        $email->setSubject(LanguageFactory::getInstance()->getDefaultLanguage()->get('wcf.contact.mail.subject'));
        $email->setBody(new MimePartFacade([
            new RecipientAwareTextMimePart('text/html', 'email_contact', 'wcf', $messageData),
            new RecipientAwareTextMimePart('text/plain', 'email_contact', 'wcf', $messageData),
        ]));
        $email->setReplyTo(new Mailbox($this->senderEmail, $this->senderName));
        $email->send();
    }

    /**
     * @param array<int, mixed> $optionValues
     * @return list<array{
     *  isMessage: false,
     *  title: string,
     *  value: string,
     *  htmlValue: string,
     * }>
     */
    private function getFormattedOptionValues(array $optionValues): array
    {
        $options = [];
        foreach ($this->getAvailableOptions() as $availableOption) {
            if (empty($optionValues[$availableOption->optionID])) {
                continue;
            }

            $value = $optionValues[$availableOption->optionID];
            $configuration = $availableOption->getConfiguration();
            $formOption = $availableOption->getFormOption();

            $options[] = [
                'isMessage' => false, // Unused, but is here for backward compatibility in third-party translations.
                'title' => LanguageFactory::getInstance()->getDefaultLanguage()->get($availableOption->optionTitle),
                'value' => $formOption->getPlainTextFormatter()->format(
                    $value,
                    LanguageFactory::getInstance()->getDefaultLanguage()->languageID,
                    $configuration
                ),
                'htmlValue' => $formOption->getFormatter()->format(
                    $value,
                    LanguageFactory::getInstance()->getDefaultLanguage()->languageID,
                    $configuration
                ),
            ];
        }

        return $options;
    }

    /**
     * @return array<int, ContactOption>
     */
    private function getAvailableOptions(): array
    {
        $optionList = new ContactOptionList();
        $optionList->getConditionBuilder()->add("contact_option.isDisabled = ?", [0]);
        $optionList->readObjects();

        return $optionList->getObjects();
    }

    /**
     * @param int[] $fileIDs
     * @return array<int, File>
     */
    private function getFiles(array $fileIDs): array
    {
        if ($fileIDs === []) {
            return [];
        }

        $list = new FileList();
        $list->setObjectIDs($fileIDs);
        $list->readObjects();

        return $list->getObjects();
    }
}
