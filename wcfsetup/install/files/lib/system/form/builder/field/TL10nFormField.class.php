<?php

namespace wcf\system\form\builder\field;

use wcf\system\form\builder\exception\InvalidFormFieldValue;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\language\I18nHandler;
use wcf\system\l10n\L10nStorage;
use wcf\util\ArrayUtil;

/**
 * Provides default implementations of `IL10nFormField` methods on top of the
 * i18n implementation.
 *
 * The l10n mode reuses the entire multilingual input machinery of the i18n
 * mode (`I18nHandler` registration, request reading, the multi-language input
 * widget and its JavaScript modules) by letting `isI18n()` return `true` in
 * l10n mode as well, but it never touches `wcf1_language_item`: There is no
 * language item pattern and the values are exposed via `getL10nValues()` for
 * persistence through `L10nStorage`. Any code that derives "phrases must be
 * saved" from `isI18n()` has to check `isL10n()` first.
 *
 * Inside forms based on `AbstractDatabaseObjectBuilderForm`, l10n fields must
 * set a `loadValueCallback` (the localized values are no longer part of the
 * object's data array) and push their values into the builder via a
 * `saveValueCallback` using `getL10nValues()`.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @mixin   IL10nFormField
 */
trait TL10nFormField
{
    use TI18nFormField {
        TI18nFormField::i18n as private i18nFieldI18n;
        TI18nFormField::languageItemPattern as private i18nFieldLanguageItemPattern;
        TI18nFormField::value as private i18nFieldValue;
        TI18nFormField::validate as private i18nFieldValidate;
        TI18nFormField::getHtmlVariables as private i18nFieldGetHtmlVariables;
    }

    /**
     * `true` if this field supports l10n input and `false` otherwise
     */
    protected bool $l10n = false;

    /**
     * `true` if this field requires a value for every language and `false` otherwise
     */
    protected bool $l10nRequired = false;

    public function l10n(bool $l10n = true): static
    {
        if ($l10n && $this->i18n) {
            throw new \BadMethodCallException(
                "The i18n mode and the l10n mode are mutually exclusive for field '{$this->getId()}'."
            );
        }

        $this->l10n = $l10n;

        return $this;
    }

    public function l10nRequired(bool $l10nRequired = true): static
    {
        $this->l10nRequired = $l10nRequired;
        $this->l10n();

        return $this;
    }

    public function isL10n(): bool
    {
        return $this->l10n;
    }

    public function isL10nRequired(): bool
    {
        return $this->l10nRequired;
    }

    /**
     * Returns `true` if this field supports i18n or l10n input, as the l10n
     * mode reuses the i18n input machinery.
     *
     * @return bool
     */
    public function isI18n()
    {
        return $this->i18n || $this->l10n;
    }

    /**
     * @param bool $i18n determines if field supports i18n input
     * @return II18nFormField this field
     */
    public function i18n(bool $i18n = true)
    {
        if ($i18n && $this->l10n) {
            throw new \BadMethodCallException(
                "The i18n mode and the l10n mode are mutually exclusive for field '{$this->getId()}'."
            );
        }

        return $this->i18nFieldI18n($i18n);
    }

    /**
     * @return II18nFormField this field
     */
    public function languageItemPattern(string $pattern)
    {
        if ($this->l10n) {
            throw new \BadMethodCallException(
                "A language item pattern cannot be used in l10n mode for field '{$this->getId()}'."
            );
        }

        return $this->i18nFieldLanguageItemPattern($pattern);
    }

    /**
     * @return static this field
     */
    public function value(mixed $value)
    {
        if (!$this->l10n) {
            return $this->i18nFieldValue($value);
        }

        // Unlike the i18n mode, a string value must not be matched against a
        // language item pattern via `setStringValue()`.
        if (\is_string($value) || \is_numeric($value)) {
            I18nHandler::getInstance()->setValue($this->getPrefixedId(), (string)$value, true);
        } elseif (\is_array($value)) {
            // A stored value map can contain `NULL` for languages that only
            // exist because of another column, drop them before dispatching.
            $value = \array_filter($value, static fn($v) => $v !== null);
            if ($value !== []) {
                if (\array_key_exists(L10nStorage::MONOLINGUAL, $value)) {
                    if (\count($value) !== 1) {
                        throw new InvalidFormFieldValue(
                            $this,
                            'monolingual value or per-language values',
                            'mixed array'
                        );
                    }

                    I18nHandler::getInstance()->setValue(
                        $this->getPrefixedId(),
                        (string)$value[L10nStorage::MONOLINGUAL],
                        true
                    );
                } else {
                    I18nHandler::getInstance()->setValues($this->getPrefixedId(), $value);
                }
            }
        } else {
            throw new InvalidFormFieldValue($this, 'string/number/array', \gettype($value));
        }

        return $this;
    }

    /**
     * @return void
     */
    public function validate()
    {
        if (!$this->l10n) {
            $this->i18nFieldValidate();

            return;
        }

        if (!empty(ArrayUtil::trim($this->getValue())) || $this->isRequired()) {
            if (
                !I18nHandler::getInstance()->validateValue(
                    $this->getPrefixedId(),
                    $this->isL10nRequired(),
                    !$this->isRequired()
                )
            ) {
                if ($this->hasPlainValue()) {
                    $this->addValidationError(new FormFieldValidationError('empty'));
                } else {
                    $this->addValidationError(new FormFieldValidationError('multilingual'));
                }
            }
        }
    }

    /**
     * @return array{}|array{elementIdentifier: string, forceSelection: bool}
     */
    public function getHtmlVariables()
    {
        if (!$this->l10n) {
            return $this->i18nFieldGetHtmlVariables();
        }

        I18nHandler::getInstance()->assignVariables();

        return [
            'elementIdentifier' => $this->getPrefixedId(),
            'forceSelection' => $this->isL10nRequired(),
        ];
    }

    public function getL10nValues(): array
    {
        if (!$this->l10n) {
            throw new \BadMethodCallException("l10n is not enabled for field '{$this->getId()}'.");
        }

        if ($this->hasI18nValues()) {
            return I18nHandler::getInstance()->getValues($this->getPrefixedId());
        }

        return [L10nStorage::MONOLINGUAL => (string)$this->getValue()];
    }
}
