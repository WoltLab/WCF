<?php

namespace wcf\system\form\builder\field;

use wcf\data\language\Language;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\language\LanguageFactory;

/**
 * Implementation of a form field for single-line text values.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
class TextFormField extends AbstractFormField implements
    IAttributeFormField,
    IAutoCompleteFormField,
    IAutoFocusFormField,
    ICssClassFormField,
    II18nFormField,
    IImmutableFormField,
    IInputModeFormField,
    IMaximumLengthFormField,
    IMinimumLengthFormField,
    IPatternFormField,
    IPlaceholderFormField
{
    use TInputAttributeFormField;
    use TTextAutoCompleteFormField;
    use TAutoFocusFormField;
    use TCssClassFormField;
    use TImmutableFormField;
    use TInputModeFormField;
    use TI18nFormField {
        validate as protected i18nValidate;
    }
    use TMaximumLengthFormField;
    use TMinimumLengthFormField;
    use TPatternFormField;
    use TPlaceholderFormField;

    /**
     * @inheritDoc
     */
    protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/Value';

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_textFormField';

    /**
     * Creates a new instance of `TextFormField`.
     */
    public function __construct()
    {
        $this->addFieldClass('long');
    }

    /**
     * @inheritDoc
     */
    protected function getValidInputModes(): array
    {
        return [
            'text',
            'tel',
            'url',
            'email',
            'numeric',
            'decimal',
            'search',
        ];
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        if ($this->isI18n()) {
            $this->i18nValidate();

            if (empty($this->getValidationErrors())) {
                $value = $this->getValue();
                if ($this->hasPlainValue()) {
                    $this->validateText($value);
                } else {
                    foreach ($value as $languageID => $languageValue) {
                        $this->validateText($languageValue, LanguageFactory::getInstance()->getLanguage($languageID));
                    }
                }
            }
        } else {
            if ($this->isRequired() && ($this->getValue() === null || $this->getValue() === '')) {
                $this->addValidationError(new FormFieldValidationError('empty'));
            } elseif ($this->getValue() !== null && $this->getValue() !== '') {
                $this->validateText($this->getValue());
            }
        }

        parent::validate();
    }

    /**
     * Checks the length of the given text with the given language.
     *
     * @param string $text validated text
     * @param null|Language $language language of validated text or `null` for monolingual text
     */
    protected function validateText($text, ?Language $language = null)
    {
        $this->validateMinimumLength($text, $language);
        $this->validateMaximumLength($text, $language);
    }

    /**
     * Returns the value for the `type` attribute of the input field.
     * @since 6.0
     */
    public function getInputType(): string
    {
        return 'text';
    }
}
