<?php

namespace wcf\acp\form;

use wcf\data\captcha\question\CaptchaQuestionAction;
use wcf\form\AbstractFormBuilderForm;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\MultilineTextFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\Regex;
use wcf\util\StringUtil;

/**
 * Shows the form to create a new captcha question.
 *
 * @property    CaptchaQuestionAction   $objectAction
 *
 * @author  Florian Gail, Matthias Schmidt
 * @copyright   2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
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

    /**
     * @inheritDoc
     */
    protected function createForm()
    {
        parent::createForm();

        $this->form->appendChildren([
            FormContainer::create('data')
                ->appendChildren([
                    TextFormField::create('question')
                        ->label('wcf.acp.captcha.question.question')
                        ->maximumLength(255)
                        ->required()
                        ->i18n()
                        ->languageItemPattern('wcf.captcha.question.question.question\d+'),
                    MultilineTextFormField::create('answers')
                        ->label('wcf.acp.captcha.question.answers')
                        ->description('wcf.acp.captcha.question.answers.description')
                        ->required()
                        ->i18n()
                        ->languageItemPattern('wcf.captcha.question.answers.question\d+')
                        ->addValidator(new FormFieldValidator('regex', function (MultilineTextFormField $formField) {
                            if ($formField->getValidationErrors() !== []) {
                                return;
                            }

                            if ($formField->hasI18nValues()) {
                                $answers = [];
                                foreach ($formField->getValue() as $value) {
                                    $answers = \array_merge($answers, \explode("\n", StringUtil::unifyNewlines($value)));
                                }
                            } else {
                                $answers = \explode("\n", StringUtil::unifyNewlines($formField->getValue()));
                            }

                            foreach ($answers as $answer) {
                                if (!$this->validateAnswer($answer)) {
                                    $formField->addValidationError(
                                        new FormFieldValidationError(
                                            'invalidRegex',
                                            'wcf.acp.captcha.question.answers.error.invalidRegex',
                                            [
                                                'invalidRegex' => $answer,
                                            ]
                                        )
                                    );
                                }
                            }
                        })),
                    BooleanFormField::create('isDisabled')
                        ->label('wcf.acp.captcha.question.isDisabled'),
                ]),
        ]);
    }

    /**
     * Validates the given answer-text.
     */
    private function validateAnswer(string $text): bool
    {
        if (!\mb_substr($text, 0, 1) == '~' || !\mb_substr($text, -1, 1) == '~') {
            return true;
        }

        $regexLength = \mb_strlen($text) - 2;

        return $regexLength && Regex::compile(\mb_substr($text, 1, $regexLength))->isValid();
    }
}
