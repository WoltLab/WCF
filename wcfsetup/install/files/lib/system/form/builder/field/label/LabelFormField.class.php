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
    protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/Value';

    /**
     * label group whose labels can be selected via this form field
     * @var ViewableLabelGroup
     */
    protected $labelGroup;

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_labelFormField';

    /**
     * loaded labels grouped by label object type and object id to avoid loading the same labels
     * over and over again for the same object and different label groups
     * @var Label[][]
     */
    protected static $loadedLabels = [];

    /**
     * Returns the label group whose labels can be selected via this form field.
     *
     * @return  ViewableLabelGroup      label group whose labels can be selected
     * @throws  \BadMethodCallException     if no label has been set
     */
    public function getLabelGroup()
    {
        if ($this->labelGroup === null) {
            throw new \BadMethodCallException("No label group has been set for field '{$this->getId()}'.");
        }

        return $this->labelGroup;
    }

    /**
     * @inheritDoc
     */
    public function getObjectTypeDefinition()
    {
        return 'com.woltlab.wcf.label.object';
    }

    /**
     * @inheritDoc
     */
    public function hasSaveValue()
    {
        return false;
    }

    /**
     * Sets the label group whose labels can be selected via this form field and returns this
     * form field.
     *
     * If no form field label has been set, the title of the label group will be set as label.
     *
     * @param ViewableLabelGroup $labelGroup label group whose labels can be selected
     * @return  static                  this form field
     */
    public function labelGroup(ViewableLabelGroup $labelGroup)
    {
        $this->labelGroup = $labelGroup;

        if ($this->label === null) {
            $this->label($this->getLabelGroup()->getTitle());
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function updatedObject(array $data, IStorableObject $object, $loadValues = true)
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
            /** @var Label $label */
            foreach (static::$loadedLabels[$objectTypeID][$objectID] as $label) {
                if (\in_array($label->labelID, $labelIDs)) {
                    $this->value($label->labelID);
                }
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function populate()
    {
        parent::populate();

        $this->getDocument()->getDataHandler()->addProcessor(new CustomFormDataProcessor(
            'label',
            function (IFormDocument $document, array $parameters) {
                // `-1` and `0` are special values that are irrelevant for saving.
                if ($this->checkDependencies() && $this->getValue() > 0) {
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

    /**
     * @inheritDoc
     */
    public function readValue()
    {
        if ($this->getDocument()->hasRequestData($this->getPrefixedId())) {
            $this->value = \intval($this->getDocument()->getRequestData($this->getPrefixedId()));
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        if ($this->isRequired()) {
            if ($this->value <= 0) {
                $this->addValidationError(new FormFieldValidationError('empty'));
            }
        } elseif ($this->value > 0 && !\in_array($this->value, $this->getLabelGroup()->getLabelIDs())) {
            $this->addValidationError(new FormFieldValidationError(
                'invalidValue',
                'wcf.global.form.error.noValidSelection'
            ));
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
    public static function createFields($objectType, array $labelGroups, $objectProperty = 'labelIDs')
    {
        $formFields = [];
        foreach ($labelGroups as $labelGroup) {
            $formFields[] = static::create($objectProperty . $labelGroup->groupID)
                ->objectProperty($objectProperty)
                ->objectType($objectType)
                ->required($labelGroup->forceSelection)
                ->labelGroup($labelGroup);
        }

        return $formFields;
    }
}
