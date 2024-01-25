<?php

namespace wcf\system\form\builder\field;

use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\style\FontAwesomeIcon;

/**
 * Implementation of a form field for to select a FontAwesome icon.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
class IconFormField extends AbstractFormField implements IImmutableFormField
{
    use TImmutableFormField;

    /**
     * @inheritDoc
     */
    protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/Value';

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_iconFormField';

    /**
     * `true` if the global icon-related JavaScript code has already been included
     * and `false` otherwise
     * @var bool
     */
    protected static $includeJavaScript = true;

    /**
     * @inheritDoc
     */
    public function getHtmlVariables()
    {
        $value = static::$includeJavaScript;
        if (static::$includeJavaScript) {
            static::$includeJavaScript = false;
        }

        return [
            '__iconFormFieldIncludeJavaScript' => $value,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getSaveValue()
    {
        if ($this->getValue()) {
            return (string)$this->getIcon();
        }

        return '';
    }

    /**
     * @inheritDoc
     */
    public function readValue()
    {
        if ($this->getDocument()->hasRequestData($this->getPrefixedId())) {
            $this->value = $this->getDocument()->getRequestData($this->getPrefixedId());
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        parent::validate();

        if (!$this->getValue()) {
            if ($this->isRequired()) {
                $this->addValidationError(new FormFieldValidationError('empty'));
            }
        } elseif (!FontAwesomeIcon::isValidString($this->getValue())) {
            $this->addValidationError(new FormFieldValidationError(
                'invalidValue',
                'wcf.global.form.error.noValidSelection'
            ));
        }
    }

    /**
     * @inheritDoc
     */
    public function value($value)
    {
        if (\str_starts_with($value, 'fa-')) {
            $value = '';
        }

        return parent::value($value);
    }

    /**
     * @since 6.0
     */
    public function getIcon(): ?FontAwesomeIcon
    {
        if ($this->value && FontAwesomeIcon::isValidString($this->value)) {
            return FontAwesomeIcon::fromString($this->value);
        }

        return null;
    }
}
