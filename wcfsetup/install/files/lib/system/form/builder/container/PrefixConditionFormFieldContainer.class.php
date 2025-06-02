<?php

namespace wcf\system\form\builder\container;

use wcf\data\IStorableObject;
use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\field\IImmutableFormField;
use wcf\system\form\builder\field\ISelectionFormField;
use wcf\system\form\builder\IFormDocument;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class PrefixConditionFormFieldContainer extends FormContainer
{
    /**
     * form field to which the prefix selection is added
     */
    protected IFormField $field;

    /**
     * selection form field containing the prefix options
     */
    protected ?ISelectionFormField $prefixField;

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_prefixFormFieldContainer';

    #[\Override]
    public function populate()
    {
        $this->getDocument()->getDataHandler()
            ->addProcessor(
                new CustomFormDataProcessor(
                    $this->getId() . "DataProcessor",
                    function (IFormDocument $document, array $parameters) {
                        $fieldId = $this->getField()->getId();
                        $prefixId = $this->getPrefixField()->getId();
                        if (isset($parameters['data'][$prefixId], $parameters['data'][$fieldId])) {
                            $parameters['data'][$this->getId()] = [
                                'condition' => $parameters['data'][$prefixId],
                                'value' => $parameters['data'][$fieldId],
                            ];
                        }

                        unset(
                            $parameters['data'][$fieldId],
                            $parameters['data'][$prefixId]
                        );

                        return $parameters;
                    },
                )
            );

        return parent::populate();
    }

    #[\Override]
    public function updatedObject(array $data, IStorableObject $object, $loadValues = true)
    {
        if ($loadValues && isset($data[$this->getId()])) {
            ["condition" => $condition, "value" => $value] = $data[$this->getId()];

            $this->getField()->updatedObject([$this->getField()->getId() => $value], $object);
            $this->getPrefixField()->updatedObject([$this->getPrefixField()->getId() => $condition], $object);
        }

        return $this;
    }

    public function field(IFormField $formField): static
    {
        if (isset($this->field)) {
            throw new \BadMethodCallException("Field has already been set for container '{$this->getId()}'.");
        }

        $this->field = $formField;
        $this->appendChild($formField);

        return $this;
    }

    public function getField(): IFormField
    {
        if (!isset($this->field)) {
            throw new \BadMethodCallException("Field has not been set yet for container '{$this->getId()}'.");
        }

        return $this->field;
    }

    /**
     * Returns the initial option of the prefix selection dropdown.
     *
     * @return array{label: string, value: mixed, depth: int, isSelectable: bool}
     * @throws \BadMethodCallException if no prefix field is set or has no options
     */
    public function getSelectedPrefixOption(): array
    {
        if (!isset($this->prefixField)) {
            throw new \BadMethodCallException(
                "There is no prefix field for which a label could be determined for container '{$this->getId()}'."
            );
        }
        if (empty($this->getPrefixField()->getOptions())) {
            throw new \BadMethodCallException(
                "The prefix field has no options for container '{$this->getId()}'."
            );
        }

        foreach ($this->getPrefixField()->getNestedOptions() as $option) {
            if ($this->getPrefixField()->getValue() === null) {
                if ($option['isSelectable']) {
                    return $option;
                }
            } elseif ($option['value'] == $this->getPrefixField()->getValue()) {
                return $option;
            }
        }

        // Return the first selectable option if no valid value is selected.
        foreach ($this->getPrefixField()->getNestedOptions() as $option) {
            if ($option['isSelectable']) {
                return $option;
            }
        }

        throw new \RuntimeException(
            "Cannot determine selected prefix option for container '{$this->getId()}'."
        );
    }

    /**
     * Returns the selection form field containing the prefix options.
     */
    public function getPrefixField(): ?ISelectionFormField
    {
        return $this->prefixField;
    }

    /**
     * Returns the label used for the prefix selection if the field has no selectable options
     * or is immutable.
     */
    public function getPrefixLabel(): string
    {
        if ($this->getPrefixField() === null) {
            throw new \BadMethodCallException(
                "There is no prefix field for which a label could be determined for container '{$this->getId()}'."
            );
        }

        if (empty($this->getPrefixField()->getOptions())) {
            return '';
        }

        if (isset($this->getPrefixField()->getOptions()[$this->getPrefixField()->getValue()])) {
            return $this->getPrefixField()->getOptions()[$this->getPrefixField()->getValue()];
        }

        return '';
    }

    /**
     * Sets the selection form field containing the prefix options.
     */
    public function prefixField(ISelectionFormField $formField): static
    {
        if (isset($this->prefixField)) {
            throw new \BadMethodCallException(
                "Prefix field has already been set for container '{$this->getId()}'."
            );
        }

        $this->prefixField = $formField;
        $this->appendChild($formField);

        return $this;
    }

    /**
     * Returns `true` if the prefix selection has any selectable options.
     */
    public function prefixHasSelectableOptions(): bool
    {
        $prefixField = $this->getPrefixField();

        if ($prefixField === null) {
            return false;
        }

        if ($prefixField instanceof IImmutableFormField && $prefixField->isImmutable()) {
            return false;
        }

        foreach ($prefixField->getNestedOptions() as $option) {
            if ($option['isSelectable']) {
                return true;
            }
        }

        return false;
    }
}
