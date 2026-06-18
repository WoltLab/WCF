<?php

namespace wcf\acp\form;

use wcf\data\captcha\question\CaptchaQuestion;
use wcf\data\captcha\question\CaptchaQuestionAction;
use wcf\data\language\Language;
use wcf\form\AbstractFormBuilderForm;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\MultilineTextFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\language\LanguageFactory;
use wcf\system\Regex;

/**
 * Shows the form to create a new captcha question.
 *
 * @author      Olaf Braun, Matthias Schmidt
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractFormBuilderForm<CaptchaQuestion>
 */
class CaptchaQuestionAddForm extends AbstractFormBuilderForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.captcha.question.add';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.captcha.canManageCaptchaQuestion'];

    /**
     * @inheritDoc
     */
    public $objectActionClass = CaptchaQuestionAction::class;

    /**
     * @inheritDoc
     */
    public $objectEditLinkController = CaptchaQuestionEditForm::class;

    #[\Override]
    protected function createForm()
    {
        parent::createForm();

        $this->form->appendChildren([
            FormContainer::create('general')
                ->appendChildren([
                    TextFormField::create('question')
                        ->label('wcf.acp.captcha.question.question')
                        ->i18n()
                        ->languageItemPattern('wcf.captcha.question.question.question\d+')
                        ->required(),
                    MultilineTextFormField::create('answers')
                        ->label('wcf.acp.captcha.question.answers')
                        ->i18n()
                        ->languageItemPattern('wcf.captcha.question.answers.question\d+')
                        ->required()
                        ->addValidator(
                            new FormFieldValidator('regexValidator', function (MultilineTextFormField $formField) {
                                $value = $formField->getValue();

                                if ($formField->hasPlainValue()) {
                                    $this->validateAnswer($value, $formField);
                                } else {
                                    foreach ($value as $languageID => $languageValue) {
                                        $this->validateAnswer(
                                            $languageValue,
                                            $formField,
                                            LanguageFactory::getInstance()->getLanguage($languageID)
                                        );
                                    }
                                }
                            })
                        ),
                    BooleanFormField::create('isDisabled')
                        ->label('wcf.acp.captcha.question.isDisabled')
                        ->value(false)
                ])
        ]);
    }

    protected function validateAnswer(
        string $answer,
        MultilineTextFormField $formField,
        ?Language $language = null
    ): void {
        if (!\str_starts_with($answer, '~') || !\str_ends_with($answer, '~')) {
            return;
        }

        $regexLength = \mb_strlen($answer) - 2;
        if (!$regexLength || !Regex::compile(\mb_substr($answer, 1, $regexLength))->isValid()) {
            $formField->addValidationError(
                new FormFieldValidationError(
                    'invalidRegex',
                    'wcf.acp.captcha.question.answers.error.invalidRegex',
                    [
                        'invalidRegex' => $answer,
                        'language' => $language
                    ]
                )
            );
        }
    }
}
