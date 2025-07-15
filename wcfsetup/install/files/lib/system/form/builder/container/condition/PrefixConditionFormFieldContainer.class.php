<?php

namespace wcf\system\form\builder\container\condition;

use wcf\data\IStorableObject;
use wcf\system\form\builder\container\FormContainer;
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
     * form field to which the prefix is added
     */
    protected IFormField $field;

    /**
     * form field containing the prefix field
     */
    protected IFormField $prefixField;

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
        $prefixField = $this->getPrefixField();

        \assert($prefixField instanceof ISelectionFormField);

        if (empty($prefixField->getOptions())) {
            throw new \BadMethodCallException(
                "The prefix field has no options for container '{$this->getId()}'."
            );
        }

        foreach ($prefixField->getNestedOptions() as $option) {
            if ($prefixField->getValue() === null) {
                if ($option['isSelectable']) {
                    return $option;
                }
            } elseif ($option['value'] == $prefixField->getValue()) {
                return $option;
            }
        }

        // Return the first selectable option if no valid value is selected.
        foreach ($prefixField->getNestedOptions() as $option) {
            if ($option['isSelectable']) {
                return $option;
            }
        }

        throw new \RuntimeException(
            "Cannot determine selected prefix option for container '{$this->getId()}'."
        );
    }

    /**
     * Returns the prefix form field.
     */
    public function getPrefixField(): IFormField
    {
        if (!isset($this->prefixField)) {
            throw new \BadMethodCallException(
                "Prefix field has not been set yet for container '{$this->getId()}'."
            );
        }

        return $this->prefixField;
    }

    /**
     * Sets the prefix form field.
     */
    public function prefixField(IFormField $formField): static
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
     * Returns `true` if the prefix form field has any selectable options.
     */
    public function prefixHasSelectableOptions(): bool
    {
        $prefixField = $this->getPrefixField();

        if (!($prefixField instanceof ISelectionFormField)) {
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
