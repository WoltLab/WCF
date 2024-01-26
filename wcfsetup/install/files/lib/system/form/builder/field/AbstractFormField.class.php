<?php

namespace wcf\system\form\builder\field;

use wcf\data\IStorableObject;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\field\validation\IFormFieldValidationError;
use wcf\system\form\builder\field\validation\IFormFieldValidator;
use wcf\system\form\builder\TFormChildNode;
use wcf\system\form\builder\TFormElement;
use wcf\system\WCF;

/**
 * Abstract implementation of a form field.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
abstract class AbstractFormField implements IFormField
{
    use TFormChildNode;
    use TFormElement;

    /**
     * name of the JavaScript data handler module used for Ajax dialogs
     * @var null|string
     */
    protected $javaScriptDataHandlerModule;

    /**
     * name of the object property this field represents
     * @var null|string
     */
    protected $objectProperty;

    /**
     * `true` if this field has to be filled out and `false` otherwise
     * @var bool
     */
    protected $required = false;

    /**
     * name of the template used to output this field
     * @var string
     */
    protected $templateName;

    /**
     * name of the template's application used to output this field
     * @var string
     */
    protected $templateApplication = 'wcf';

    /**
     * validation errors of this field
     * @var IFormFieldValidationError[]
     */
    protected $validationErrors = [];

    /**
     * field value validators of this field
     * @var IFormFieldValidator[]
     */
    protected $validators = [];

    /**
     * value of the field
     * @var mixed
     */
    protected $value;

    /**
     * @inheritDoc
     */
    public function addValidationError(IFormFieldValidationError $error)
    {
        if (empty($this->validationErrors)) {
            $this->addClass('formError');
        }

        $this->validationErrors[] = $error;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addValidator(IFormFieldValidator $validator)
    {
        if ($this->hasValidator($validator->getId())) {
            throw new \InvalidArgumentException("Validator with id '{$validator->getId()}' already exists for field '{$this->getId()}'.");
        }

        $this->validators[$validator->getId()] = $validator;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getFieldHtml()
    {
        if ($this->templateName === null) {
            throw new \LogicException("\$templateName property has not been set for class '" . static::class . "'.");
        }

        return WCF::getTPL()->fetch(
            $this->templateName,
            $this->templateApplication,
            \array_merge($this->getHtmlVariables(), [
                'field' => $this,
            ]),
            true
        );
    }

    /**
     * @inheritDoc
     */
    public function getHtml()
    {
        if ($this->requiresLabel() && $this->getLabel() === null) {
            throw new \UnexpectedValueException("Form field '{$this->getPrefixedId()}' requires a label.");
        }

        return WCF::getTPL()->fetch(
            'shared_formField',
            'wcf',
            ['field' => $this],
            true
        );
    }

    /**
     * @inheritDoc
     */
    public function getJavaScriptDataHandlerModule()
    {
        return $this->javaScriptDataHandlerModule;
    }

    /**
     * @inheritDoc
     */
    public function getObjectProperty()
    {
        if ($this->objectProperty !== null) {
            return $this->objectProperty;
        }

        return $this->getId();
    }

    /**
     * @inheritDoc
     */
    public function getSaveValue()
    {
        return $this->getValue();
    }

    /**
     * @inheritDoc
     */
    public function getValidationErrors()
    {
        return $this->validationErrors;
    }

    /**
     * @inheritDoc
     */
    public function getValidators()
    {
        return $this->validators;
    }

    /**
     * @inheritDoc
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @inheritDoc
     */
    public function hasValidator($validatorId)
    {
        FormFieldValidator::validateId($validatorId);

        return isset($this->validators[$validatorId]);
    }

    /**
     * @inheritDoc
     */
    public function hasSaveValue()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * @inheritDoc
     */
    public function updatedObject(array $data, IStorableObject $object, $loadValues = true)
    {
        if ($this instanceof IImmutableFormField && $this->isImmutable()) {
            $loadValues = true;
        }

        if ($loadValues && isset($data[$this->getObjectProperty()])) {
            $this->value($data[$this->getObjectProperty()]);
        }

        return $this;
    }

    /**
     * @inheritDoc
     * @return  static
     */
    public function objectProperty($objectProperty)
    {
        if ($objectProperty === '') {
            $this->objectProperty = null;
        } else {
            static::validateId($objectProperty);

            $this->objectProperty = $objectProperty;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function removeValidator($validatorId)
    {
        if (!$this->hasValidator($validatorId)) {
            throw new \InvalidArgumentException("Unknown validator with id '{$validatorId}' for field '{$this->getId()}'.");
        }

        unset($this->validators[$validatorId]);

        return $this;
    }

    /**
     * @inheritDoc
     * @return  static
     */
    public function required($required = true)
    {
        $this->required = $required;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function value($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        // does nothing
    }
}
