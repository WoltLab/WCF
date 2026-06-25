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
     * callback transferring this field's save value into a `DatabaseObjectBuilder`
     * @var ?\Closure(\wcf\data\DatabaseObjectBuilder<*>, IFormField): mixed
     * @since 6.3
     */
    protected ?\Closure $saveValueCallback = null;

    #[\Override]
    public function addValidationError(IFormFieldValidationError $error)
    {
        if (empty($this->validationErrors)) {
            $this->addClass('formError');
        }

        $this->validationErrors[] = $error;

        return $this;
    }

    #[\Override]
    public function addValidator(IFormFieldValidator $validator)
    {
        if ($this->hasValidator($validator->getId())) {
            throw new \InvalidArgumentException("Validator with id '{$validator->getId()}' already exists for field '{$this->getId()}'.");
        }

        $this->validators[$validator->getId()] = $validator;

        return $this;
    }

    #[\Override]
    public function getFieldHtml()
    {
        if ($this->templateName === null) {
            throw new \LogicException("\$templateName property has not been set for class '" . static::class . "'.");
        }

        return WCF::getTPL()->render(
            $this->templateApplication,
            $this->templateName,
            \array_merge($this->getHtmlVariables(), [
                'field' => $this,
            ])
        );
    }

    #[\Override]
    public function getHtml()
    {
        if ($this->requiresLabel() && $this->getLabel() === null) {
            throw new \UnexpectedValueException("Form field '{$this->getPrefixedId()}' requires a label.");
        }

        return WCF::getTPL()->render(
            'wcf',
            'shared_formField',
            ['field' => $this],
        );
    }

    #[\Override]
    public function getJavaScriptDataHandlerModule()
    {
        return $this->javaScriptDataHandlerModule;
    }

    #[\Override]
    public function getObjectProperty()
    {
        if ($this->objectProperty !== null) {
            return $this->objectProperty;
        }

        return $this->getId();
    }

    #[\Override]
    public function getSaveValue()
    {
        return $this->getValue();
    }

    #[\Override]
    public function getValidationErrors()
    {
        return $this->validationErrors;
    }

    #[\Override]
    public function getValidators()
    {
        return $this->validators;
    }

    #[\Override]
    public function getValue()
    {
        return $this->value;
    }

    #[\Override]
    public function saveValueCallback(\Closure $callback): static
    {
        $this->saveValueCallback = $callback;

        return $this;
    }

    #[\Override]
    public function getSaveValueCallback(): ?\Closure
    {
        return $this->saveValueCallback;
    }

    #[\Override]
    public function hasValidator(string $validatorId)
    {
        FormFieldValidator::validateId($validatorId);

        return isset($this->validators[$validatorId]);
    }

    #[\Override]
    public function hasSaveValue()
    {
        return true;
    }

    #[\Override]
    public function isRequired()
    {
        return $this->required;
    }

    #[\Override]
    public function updatedObject(array $data, IStorableObject $object, bool $loadValues = true)
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
     * @return  static
     */
    #[\Override]
    public function objectProperty(string $objectProperty)
    {
        if ($objectProperty === '') {
            $this->objectProperty = null;
        } else {
            static::validateId($objectProperty);

            $this->objectProperty = $objectProperty;
        }

        return $this;
    }

    #[\Override]
    public function removeValidator(string $validatorId)
    {
        if (!$this->hasValidator($validatorId)) {
            throw new \InvalidArgumentException("Unknown validator with id '{$validatorId}' for field '{$this->getId()}'.");
        }

        unset($this->validators[$validatorId]);

        return $this;
    }

    /**
     * @return  static
     */
    #[\Override]
    public function required(bool $required = true)
    {
        $this->required = $required;

        return $this;
    }

    #[\Override]
    public function value(mixed $value)
    {
        $this->value = $value;

        return $this;
    }

    #[\Override]
    public function validate()
    {
        // does nothing
    }
}
