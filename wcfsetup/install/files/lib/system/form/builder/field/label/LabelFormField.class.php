<?php

namespace wcf\system\form\builder\field\label;

use wcf\data\IStorableObject;
use wcf\data\label\group\ViewableLabelGroup;
use wcf\data\label\Label;
use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\IFormDocument;
use wcf\system\form\builder\IObjectTypeFormNode;
use wcf\system\form\builder\TObjectTypeFormNode;
use wcf\system\label\LabelHandler;

/**
 * Implementation of a form field to select labels.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
final class LabelFormField extends AbstractFormField implements IObjectTypeFormNode
{
    use TObjectTypeFormNode;

    /**
     * @inheritDoc
     */
    protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/Label';

    /**
     * `true` if the value `-1` can be selected for objects without any label of the label group
     * @since 6.3
     */
    protected bool $invertible = false;

    /**
     * label group whose labels can be selected via this form field
     */
    protected ?ViewableLabelGroup $labelGroup = null;

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_labelFormField';

    /**
     * maximum number of labels that can be selected, the value is a list of label ids if more
     * than one label can be selected
     * @since 6.3
     */
    protected int $maxSelection = 1;

    /**
     * loaded labels grouped by label object type and object id to avoid loading the same labels
     * over and over again for the same object and different label groups
     * @var Label[][]
     */
    protected static array $loadedLabels = [];

    /**
     * Returns the label group whose labels can be selected via this form field.
     *
     * @throws  \BadMethodCallException     if no label has been set
     */
    public function getLabelGroup(): ViewableLabelGroup
    {
        if ($this->labelGroup === null) {
            throw new \BadMethodCallException("No label group has been set for field '{$this->getId()}'.");
        }

        return $this->labelGroup;
    }

    #[\Override]
    public function getObjectTypeDefinition(): string
    {
        return 'com.woltlab.wcf.label.object';
    }

    #[\Override]
    public function hasSaveValue(): bool
    {
        return false;
    }

    /**
     * Sets whether the value `-1` can be selected for objects without any label of the label
     * group and returns this form field.
     *
     * Per default, the value `-1` cannot be selected.
     *
     * @since 6.3
     */
    public function invertible(bool $invertible = true): static
    {
        if ($invertible && $this->maxSelection > 1) {
            throw new \BadMethodCallException("Field '{$this->getId()}' cannot be invertible if more than one label can be selected.");
        }

        $this->invertible = $invertible;

        return $this;
    }

    /**
     * Returns `true` if the value `-1` can be selected for objects without any label of the label
     * group and returns `false` otherwise.
     *
     * @since 6.3
     */
    public function isInvertible(): bool
    {
        return $this->invertible;
    }

    /**
     * Sets the maximum number of labels that can be selected and returns this form field.
     *
     * If more than one label can be selected, the value of this field is a list of label ids.
     * Per default, only a single label can be selected.
     *
     * @since 6.3
     */
    public function maxSelection(int $maxSelection): static
    {
        if ($maxSelection < 1) {
            throw new \InvalidArgumentException("The maximum selection of field '{$this->getId()}' must be at least 1.");
        }
        if ($maxSelection > 1 && $this->isInvertible()) {
            throw new \BadMethodCallException("Field '{$this->getId()}' cannot select more than one label because it is invertible.");
        }

        $this->maxSelection = $maxSelection;

        return $this;
    }

    /**
     * Returns the maximum number of labels that can be selected.
     *
     * @since 6.3
     */
    public function getMaxSelection(): int
    {
        return $this->maxSelection;
    }

    /**
     * Returns the ids of the selected labels, ignoring the special values `0` and `-1`.
     *
     * @return list<int>
     * @since 6.3
     */
    public function getSelectedLabelIDs(): array
    {
        if (\is_array($this->getValue())) {
            return $this->getValue();
        }

        return $this->getValue() > 0 ? [$this->getValue()] : [];
    }

    /**
     * Sets the label group whose labels can be selected via this form field and returns this
     * form field.
     *
     * If no form field label has been set, the title of the label group will be set as label.
     *
     * @param ViewableLabelGroup $labelGroup label group whose labels can be selected
     */
    public function labelGroup(ViewableLabelGroup $labelGroup): static
    {
        $this->labelGroup = $labelGroup;

        if ($this->label === null) {
            $this->label($this->getLabelGroup()->getTitle());
        }

        return $this;
    }

    #[\Override]
    public function updatedObject(array $data, IStorableObject $object, bool $loadValues = true): static
    {
        if ($loadValues) {
            $objectTypeID = $this->getObjectType()->objectTypeID;
            $objectID = $object->{$object::getDatabaseTableIndexName()};

            if (!isset(static::$loadedLabels[$objectTypeID])) {
                static::$loadedLabels[$objectTypeID] = [];
            }
            if (!isset(static::$loadedLabels[$objectTypeID][$objectID])) {
                $assignedLabels = LabelHandler::getInstance()->getAssignedLabels(
                    $objectTypeID,
                    [$objectID]
                );
                static::$loadedLabels[$objectTypeID][$objectID] = $assignedLabels[$objectID] ?? [];
            }

            $labelIDs = $this->getLabelGroup()->getLabelIDs();
            $selectedLabelIDs = [];
            /** @var Label $label */
            foreach (static::$loadedLabels[$objectTypeID][$objectID] as $label) {
                if (\in_array($label->labelID, $labelIDs, true)) {
                    $selectedLabelIDs[] = $label->labelID;
                }
            }

            if ($this->maxSelection > 1) {
                $this->value($selectedLabelIDs);
            } elseif ($selectedLabelIDs !== []) {
                $this->value(\end($selectedLabelIDs));
            }
        }

        return $this;
    }

    #[\Override]
    public function populate(): static
    {
        parent::populate();

        $this->getDocument()->getDataHandler()->addProcessor(new CustomFormDataProcessor(
            'label',
            function (IFormDocument $document, array $parameters) {
                if (!$this->checkDependencies()) {
                    return $parameters;
                }

                if ($this->maxSelection > 1) {
                    if ($this->getSelectedLabelIDs() !== []) {
                        $parameters[$this->getObjectProperty()][$this->getLabelGroup()->groupID] = $this->getSelectedLabelIDs();
                    }

                    return $parameters;
                }

                // `0` is irrelevant for saving, `-1` is only relevant for invertible fields.
                if ($this->getValue() > 0 || ($this->getValue() === -1 && $this->isInvertible())) {
                    if (!isset($parameters[$this->getObjectProperty()])) {
                        $parameters[$this->getObjectProperty()] = [];
                    }

                    $parameters[$this->getObjectProperty()][$this->getLabelGroup()->groupID] = $this->getValue();
                }

                return $parameters;
            }
        ));

        return $this;
    }

    #[\Override]
    public function readValue(): static
    {
        if ($this->getDocument()->hasRequestData($this->getPrefixedId())) {
            $value = $this->getDocument()->getRequestData($this->getPrefixedId());

            if ($this->maxSelection > 1) {
                $labelIDs = \array_map(static fn($labelID) => (int)$labelID, (array)$value);
                $this->value = \array_values(\array_unique(\array_filter(
                    $labelIDs,
                    static fn(int $labelID) => $labelID > 0
                )));
            } elseif (!\is_array($value)) {
                $this->value = (int)$value;
            }
        }

        return $this;
    }

    #[\Override]
    public function validate(): void
    {
        if ($this->maxSelection > 1) {
            $labelIDs = $this->getSelectedLabelIDs();
            if ($labelIDs === []) {
                if ($this->isRequired()) {
                    $this->addValidationError(new FormFieldValidationError('empty'));
                }

                return;
            }

            if (\array_diff($labelIDs, $this->getLabelGroup()->getLabelIDs()) !== []) {
                $this->addValidationError(new FormFieldValidationError(
                    'invalidValue',
                    'wcf.global.form.error.noValidSelection'
                ));
            } elseif (\count($labelIDs) > $this->maxSelection) {
                $this->addValidationError(new FormFieldValidationError(
                    'maximumMultiples',
                    'wcf.form.field.label.error.maximumMultiples',
                    [
                        'count' => \count($labelIDs),
                        'maximumCount' => $this->maxSelection,
                    ]
                ));
            }

            return;
        }

        if ($this->value > 0) {
            if (!\in_array($this->value, $this->getLabelGroup()->getLabelIDs())) {
                $this->addValidationError(new FormFieldValidationError(
                    'invalidValue',
                    'wcf.global.form.error.noValidSelection'
                ));
            }
        } elseif ($this->value === -1 && $this->isInvertible()) {
            return;
        } elseif ($this->isRequired()) {
            $this->addValidationError(new FormFieldValidationError('empty'));
        }
    }

    /**
     * Returns label group fields based for the given label groups using the given object property.
     *
     * The id of each form fields is `{$objectProperty}{$labelGroupID}`.
     *
     * @param string $objectType `com.woltlab.wcf.label.object` object type
     * @param ViewableLabelGroup[] $labelGroups label groups for which label form fields are created
     * @param string $objectProperty object property of form fields
     * @return  static[]
     */
    public static function createFields(string $objectType, array $labelGroups, string $objectProperty = 'labelIDs'): array
    {
        $formFields = [];
        foreach ($labelGroups as $labelGroup) {
            $formFields[] = static::create($objectProperty . $labelGroup->groupID)
                ->objectProperty($objectProperty)
                ->objectType($objectType)
                ->required((bool)$labelGroup->forceSelection)
                ->maxSelection(LabelHandler::getInstance()->getMaxSelection($labelGroup, $objectType))
                ->labelGroup($labelGroup);
        }

        return $formFields;
    }
}
