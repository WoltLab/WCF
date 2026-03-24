<?php

namespace wcf\system\form\builder\field;

use wcf\data\language\Language;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\language\LanguageFactory;
use wcf\util\UserUtil;

/**
 * Implementation of a form field for an email address.
 *
 * The default label of fields of this class is `wcf.form.field.email`.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
class EmailFormField extends AbstractFormField implements
    IAttributeFormField,
    IAutoCompleteFormField,
    IAutoFocusFormField,
    ICssClassFormField,
    II18nFormField,
    IImmutableFormField,
    IInputModeFormField,
    IPatternFormField,
    IPlaceholderFormField
{
    use TAttributeFormField {
        getReservedFieldAttributes as private defaultGetReservedFieldAttributes;
    }
    use TAutoCompleteFormField;
    use TAutoFocusFormField;
    use TCssClassFormField;
    use TImmutableFormField;
    use TInputModeFormField;
    use TI18nFormField {
        validate as protected i18nValidate;
    }
    use TPatternFormField;
    use TPlaceholderFormField;

    /**
     * @inheritDoc
     */
    protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/Value';

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_emailFormField';

    #[\Override]
    public function __construct()
    {
        $this->label('wcf.form.field.email');
        $this->addFieldClass('long');
        $this->inputMode('email');
    }

    /**
     * @return string[]
     * @since 5.4
     */
    #[\Override]
    protected function getValidAutoCompleteTokens(): array
    {
        return \array_merge(['email'], \array_map(static function (string $context): string {
            return $context . ' email';
        }, ['home', 'work', 'mobile', 'fax', 'pager']));
    }

    /**
     * @return string[]
     */
    protected function getValidInputModes(): array
    {
        return ['email'];
    }

    #[\Override]
    public function validate()
    {
        if ($this->isI18n()) {
            $this->i18nValidate();

            if (empty($this->getValidationErrors())) {
                $value = $this->getValue();
                if ($this->hasPlainValue()) {
                    $this->validateEmail($value);
                } else {
                    foreach ($value as $languageID => $languageValue) {
                        $this->validateEmail($languageValue, LanguageFactory::getInstance()->getLanguage($languageID));
                    }
                }
            }
        } else {
            if ($this->isRequired() && ($this->getValue() === null || $this->getValue() === '')) {
                $this->addValidationError(new FormFieldValidationError('empty'));
            } else {
                $this->validateEmail($this->getValue());
            }
        }

        parent::validate();
    }

    /**
     * Validates the given email address in the given language.
     *
     * @param ?string $email validated email address
     * @param ?Language $language language of validated email address or `null` for monolingual email address
     * @return void
     */
    protected function validateEmail($email, ?Language $language = null)
    {
        if ($email === null || $email === '') {
            return;
        }

        if (!UserUtil::isValidEmail($email)) {
            $this->addValidationError(new FormFieldValidationError(
                'invalidEmail',
                'wcf.form.field.email.error.invalidEmail',
                ['language' => $language]
            ));
        }
    }

    /**
     * @return string[]
     * @since 5.4
     */
    #[\Override]
    protected static function getReservedFieldAttributes(): array
    {
        return \array_merge(
            // @phpstan-ignore staticClassAccess.privateMethod
            static::defaultGetReservedFieldAttributes(),
            [
                'maxlength',
            ]
        );
    }
}
