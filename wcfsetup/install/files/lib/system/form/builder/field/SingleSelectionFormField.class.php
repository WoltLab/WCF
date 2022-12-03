<?php

namespace wcf\system\form\builder\field;

use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\WCF;

/**
 * Implementation of a form field for selecting a single value.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Form\Builder\Field
 * @since   5.2
 */
class SingleSelectionFormField extends AbstractFormField implements
    ICssClassFormField,
    IImmutableFormField,
    IFilterableSelectionFormField,
    INullableFormField
{
    use TCssClassFormField;
    use TImmutableFormField;
    use TFilterableSelectionFormField {
        filterable as protected traitFilterable;
    }
    use TNullableFormField;

    /**
     * @inheritDoc
     */
    protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/Value';

    /**
     * @inheritDoc
     */
    protected $templateName = '__singleSelectionFormField';

    /**
     * @var bool
     */
    protected bool $allowEmptySelection = false;

    /**
     * @var string
     */
    protected string $emptyOptionLanguageItem = 'wcf.global.noSelection';

    /**
     * @var string|int|float
     */
    protected string|int|float $emptyOptionValue = 0;

    /**
     * @inheritDoc
     */
    public function getSaveValue()
    {
        if (
            $this->allowsEmptySelection()
            && $this->isNullable()
            && $this->getValue() === $this->getEmptyOptionValue()
        ) {
            return null;
        }

        return parent::getSaveValue();
    }

    /**
     * @inheritDoc
     */
    public function filterable($filterable = true)
    {
        if ($filterable) {
            $this->javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/RadioButton';
        } else {
            $this->javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/Value';
        }

        return $this->traitFilterable($filterable);
    }

    /**
     * @inheritDoc
     */
    public function readValue()
    {
        if ($this->getDocument()->hasRequestData($this->getPrefixedId())) {
            $value = $this->getDocument()->getRequestData($this->getPrefixedId());

            if (\is_string($value)) {
                $this->value = $value;
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        if ($this->allowsEmptySelection() && $this->getValue() === $this->getEmptyOptionValue()) {
            AbstractFormField::validate();

            return;
        }

        if (!isset($this->getOptions()[$this->getValue()])) {
            $this->addValidationError(new FormFieldValidationError(
                'invalidValue',
                'wcf.global.form.error.noValidSelection'
            ));
        }

        parent::validate();
    }

    /**
     * @inheritDoc
     */
    public function value($value)
    {
        // ignore `null` as value which can be passed either for nullable
        // fields or as value if no options are available
        if ($value === null) {
            return $this;
        }

        if (!isset($this->getOptions()[$value])) {
            throw new \InvalidArgumentException("Unknown value '{$value}' for field '{$this->getId()}'.");
        }

        return parent::value($value);
    }

    /**
     * @inheritDoc
     */
    public function getOptions(): array
    {
        $options = parent::getOptions();

        if ($this->allowsEmptySelection()) {
            $options[$this->getEmptyOptionValue()] = $this->getEmptyOptionLabel();
        }

        return $options;
    }

    /**
     * @inheritDoc
     */
    public function getNestedOptions(): array
    {
        $options = parent::getNestedOptions();

        if ($this->allowsEmptySelection()) {
            $options = \array_merge([
                [
                    'depth' => 0,
                    'isSelectable' => true,
                    'label' => $this->getEmptyOptionLabel(),
                    'value' => $this->getEmptyOptionValue(),
                ],
            ], $options);
        }

        return $options;
    }

    /**
     * @since 6.0
     */
    public function allowEmptySelection(
        bool $allowEmptySelection = true,
        string $languageItem = 'wcf.global.noSelection'
    ): self {
        $this->allowEmptySelection = $allowEmptySelection;
        $this->emptyOptionLanguageItem = $languageItem;

        return $this;
    }

    /**
     * @since 6.0
     */
    public function emptyOptionValue(string|int|float $value): self
    {
        $this->emptyOptionValue = $value;

        return $this;
    }

    /**
     * @since 6.0
     */
    public function allowsEmptySelection(): bool
    {
        return $this->allowEmptySelection;
    }

    /**
     * @since 6.0
     */
    public function getEmptyOptionLabel(): string
    {
        return WCF::getLanguage()->get($this->emptyOptionLanguageItem);
    }

    /**
     * @since 6.0
     */
    public function getEmptyOptionValue(): string|int|float
    {
        return $this->emptyOptionValue;
    }
}
