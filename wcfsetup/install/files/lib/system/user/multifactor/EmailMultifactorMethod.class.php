<?php

namespace wcf\system\user\multifactor;

use wcf\system\background\BackgroundQueueHandler;
use wcf\system\email\SimpleEmail;
use wcf\system\flood\FloodControl;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\ButtonFormField;
use wcf\system\form\builder\field\RejectEverythingFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\IFormDocument;
use wcf\system\form\builder\LanguageItemFormNode;
use wcf\system\user\multifactor\email\CodeFormField;
use wcf\system\WCF;

/**
 * Implementation of one time codes sent via email.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.4
 */
final class EmailMultifactorMethod implements IMultifactorMethod
{
    private const LIFETIME = 10 * 60;

    private const REFRESH_AFTER = 2 * 60;

    public const LENGTH = 8;

    private const USER_ATTEMPTS_PER_TEN_MINUTES = 5;

    /**
     * Returns an empty string.
     */
    public function getStatusText(Setup $setup): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function createManagementForm(IFormDocument $form, ?Setup $setup, $returnData = null): void
    {
        $form->addDefaultButton(false);
        $form->successMessage('wcf.user.security.multifactor.email.success');

        if ($setup) {
            $statusContainer = FormContainer::create('enabledContainer')
                ->label('wcf.user.security.multifactor.email.enabled')
                ->appendChildren([
                    LanguageItemFormNode::create('enabled')
                        ->languageItem('wcf.user.security.multifactor.email.enabled.description'),
                    RejectEverythingFormField::create(),
                ]);
            $form->appendChild($statusContainer);
        } else {
            $generateContainer = FormContainer::create('enableContainer')
                ->label('wcf.user.security.multifactor.email.enable')
                ->appendChildren([
                    LanguageItemFormNode::create('explanation')
                        ->languageItem('wcf.user.security.multifactor.email.enable.description'),
                    ButtonFormField::create('enable')
                        ->buttonLabel('wcf.user.security.multifactor.email.enable')
                        ->objectProperty('action')
                        ->value('enable')
                        ->addValidator(new FormFieldValidator('enable', static function (ButtonFormField $field) {
                            if ($field->getValue() === null) {
                                $field->addValidationError(new FormFieldValidationError('unreachable', 'unreachable'));
                            }
                        })),
                ]);
            $form->appendChild($generateContainer);
        }
    }

    /**
     * @inheritDoc
     */
    public function processManagementForm(IFormDocument $form, Setup $setup): void
    {
        $formData = $form->getData();

        \assert(
            !empty($formData['action'])
                && $formData['action'] === 'enable'
        );
    }

    /**
     * Returns a code from $codes matching the $userCode. `null` is returned if
     * no matching code could be found.
     *
     * @param mixed[][] $codes
     * @return mixed[]
     */
    private function findValidCode(string $userCode, array $codes): ?array
    {
        $result = null;
        foreach ($codes as $code) {
            if (\hash_equals($code['code'], $userCode)) {
                $result = $code;
            }
        }

        return $result;
    }

    /**
     * Sends the email containing the one time code.
     */
    private function sendEmail(Setup $setup, string $code): void
    {
        $email = new SimpleEmail();
        $email->setRecipient($setup->getUser());
        $email->setMessageID(\sprintf(
            'com.woltlab.wcf.multifactor.email/%d/%d/%s',
            $setup->getUser()->userID,
            TIME_NOW,
            \bin2hex(\random_bytes(8))
        ));

        $email->setSubject(
            WCF::getLanguage()->getDynamicVariable('wcf.user.security.multifactor.email.subject', [
                'code' => $code,
            ])
        );
        $email->setHtmlMessage(
            WCF::getLanguage()->getDynamicVariable('wcf.user.security.multifactor.email.body.html', [
                'code' => $code,
            ])
        );
        $email->setMessage(
            WCF::getLanguage()->getDynamicVariable('wcf.user.security.multifactor.email.body.plain', [
                'code' => $code,
            ])
        );

        $jobs = $email->getEmail()->getJobs();
        foreach ($jobs as $job) {
            BackgroundQueueHandler::getInstance()->performJob($job);
        }
    }

    /**
     * @inheritDoc
     */
    public function createAuthenticationForm(IFormDocument $form, Setup $setup): void
    {
        $sql = "SELECT  code, createTime
                FROM    wcf1_user_multifactor_email
                WHERE   setupID = ?
                    AND createTime > ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $setup->getId(),
            (\TIME_NOW - self::LIFETIME),
        ]);
        $codes = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $lastCode = 0;
        foreach ($codes as $code) {
            $lastCode = \max($lastCode, $code['createTime']);
        }

        if ($lastCode < (\TIME_NOW - self::REFRESH_AFTER)) {
            // @phpstan-ignore function.alreadyNarrowedType, smallerOrEqual.alwaysTrue
            \assert(self::LENGTH <= 9, "Code does not fit into a 32-bit integer.");

            $code = \random_int(
                10 ** (self::LENGTH - 1),
                (10 ** self::LENGTH) - 1
            );
            $sql = "INSERT INTO wcf1_user_multifactor_email
                                (setupID, code, createTime)
                    VALUES      (?, ?, ?)";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([
                $setup->getId(),
                $code,
                \TIME_NOW,
            ]);

            $this->sendEmail($setup, (string)$code);
            $lastCode = \TIME_NOW;
        }

        $address = $setup->getUser()->email;
        $atSign = \strrpos($address, '@');
        $emailDomain = \substr($address, $atSign + 1);

        $form->appendChildren([
            CodeFormField::create()
                ->label('wcf.user.security.multifactor.email.code')
                ->description('wcf.user.security.multifactor.email.code.description', [
                    'emailDomain' => $emailDomain,
                    'lastCode' => $lastCode,
                ])
                ->autoFocus()
                ->required()
                ->addValidator(new FormFieldValidator('code', function (TextFormField $field) use ($codes, $setup) {
                    FloodControl::getInstance()->registerUserContent(
                        'com.woltlab.wcf.multifactor.email',
                        $setup->getId()
                    );
                    $attempts = FloodControl::getInstance()->countUserContent(
                        'com.woltlab.wcf.multifactor.email',
                        $setup->getId(),
                        new \DateInterval('PT10M')
                    );
                    if ($attempts['count'] > self::USER_ATTEMPTS_PER_TEN_MINUTES) {
                        $field->value('');
                        $field->addValidationError(new FormFieldValidationError(
                            'flood',
                            'wcf.user.security.multifactor.email.error.flood',
                            $attempts
                        ));

                        return;
                    }

                    $userCode = $field->getValue();

                    if ($this->findValidCode($userCode, $codes) === null) {
                        $field->value('');
                        $field->addValidationError(new FormFieldValidationError(
                            'invalidCode',
                            'wcf.user.security.multifactor.error.invalidCode'
                        ));
                    }
                })),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function processAuthenticationForm(IFormDocument $form, Setup $setup): void
    {
        $this->invalidateUsedCode(
            $setup->getId(),
            $form->getData()['data']['code']
        );
    }

    private function invalidateUsedCode(int $id, string $code): void
    {
        $sql = "DELETE FROM wcf1_user_multifactor_email
                WHERE       setupID = ?
                        AND createTime > ?
                        AND code = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $id,
            (\TIME_NOW - self::LIFETIME),
            $code,
        ]);
    }

    /**
     * Deletes expired codes.
     */
    public static function prune(): void
    {
        $sql = "DELETE FROM wcf1_user_multifactor_email
                WHERE       createTime < ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            (\TIME_NOW - self::LIFETIME),
        ]);
    }
}
