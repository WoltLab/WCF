<?php

namespace wcf\acp\form;

use wcf\command\captcha\question\CreateCaptchaQuestion;
use wcf\command\captcha\question\UpdateCaptchaQuestion;
use wcf\data\captcha\question\CaptchaQuestion;
use wcf\data\captcha\question\CaptchaQuestionBuilder;
use wcf\data\DatabaseObjectBuilder;
use wcf\data\language\Language;
use wcf\form\AbstractDatabaseObjectBuilderForm;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\IFormField;
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
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectBuilderForm<CaptchaQuestion, CaptchaQuestionBuilder>
 */
class CaptchaQuestionAddForm extends AbstractDatabaseObjectBuilderForm
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
    public string $objectEditLinkController = CaptchaQuestionEditForm::class;

    #[\Override]
    protected function getDatabaseObjectBuilder(): CaptchaQuestionBuilder
    {
        if ($this->formObject !== null) {
            return CaptchaQuestionBuilder::forUpdate($this->formObject);
        }

        return CaptchaQuestionBuilder::forCreate();
    }

    #[\Override]
    protected function getCommand(DatabaseObjectBuilder $builder): callable
    {
        if ($this->formObject !== null) {
            return new UpdateCaptchaQuestion($builder);
        }

        return new CreateCaptchaQuestion($builder);
    }

    #[\Override]
    protected function createForm(): void
    {
        $this->form->appendChildren([
            FormContainer::create('general')
                ->appendChildren([
                    TextFormField::create('question')
                        ->label('wcf.acp.captcha.question.question')
                        ->l10n()
                        ->required()
                        ->maximumLength(255)
                        ->saveValueCallback(static function (CaptchaQuestionBuilder $builder, TextFormField $field) {
                            $builder->setQuestion($field->getL10nValues());
                        })
                        ->loadValueCallback(static function (CaptchaQuestion $object, IFormField $field) {
                            $field->value($object->getL10nValues('question'));
                        }),
                    MultilineTextFormField::create('answers')
                        ->label('wcf.acp.captcha.question.answers')
                        ->l10n()
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
                        )
                        ->saveValueCallback(static function (CaptchaQuestionBuilder $builder, MultilineTextFormField $field) {
                            $builder->setAnswers($field->getL10nValues());
                        })
                        ->loadValueCallback(static function (CaptchaQuestion $object, IFormField $field) {
                            $field->value($object->getL10nValues('answers'));
                        }),
                    BooleanFormField::create('isDisabled')
                        ->label('wcf.acp.captcha.question.isDisabled')
                        ->value(false)
                        ->saveValueCallback(static function (CaptchaQuestionBuilder $builder, IFormField $field) {
                            $builder->setIsDisabled((bool)$field->getSaveValue());
                        })
                        ->loadValueCallback(static function (CaptchaQuestion $object, IFormField $field) {
                            $field->value($object->isDisabled);
                        }),
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
