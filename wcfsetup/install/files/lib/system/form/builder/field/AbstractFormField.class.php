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
     * @var ?string
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
     * @var ?\Closure(\wcf\data\DatabaseObjectBuilder<*>, static): void
     * @since 6.3
     */
    protected ?\Closure $saveValueCallback = null;

    /**
     * callback loading this field's value from an `IStorableObject`
     * @var ?\Closure(\wcf\data\IStorableObject, static): void
     * @since 6.3
     */
    protected ?\Closure $loadValueCallback = null;

    #[\Override]
    public function addValidationError(IFormFieldValidationError $error)
    {
        if ($this->validationErrors === []) {
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

    /**
     * Returns the id of the element the label of this field refers to or `null` if this field
     * consists of multiple elements and is thus rendered as a group labelled by its label.
     *
     * @since 6.3
     */
    public function getLabelTarget(): ?string
    {
        return $this->getPrefixedId();
    }

    /**
     * Returns `true` if this field consists of multiple elements and is rendered as a group.
     *
     * @since 6.3
     */
    public function isGroup(): bool
    {
        return $this->getLabelTarget() === null;
    }

    /**
     * Returns the ARIA attributes that link the labelled element of this field (or the group
     * element in case of a group) to the field's error messages and description.
     *
     * @return array<string, string>
     * @since 6.3
     */
    public function getAriaAttributes(): array
    {
        $attributes = [];
        $describedBy = [];
        $hasErrors = $this->getValidationErrors() !== [];

        if ($this->isGroup()) {
            // `aria-invalid` and `aria-errormessage` are not supported by `role="group"`
            if ($hasErrors) {
                $describedBy[] = $this->getPrefixedId() . 'Error';
            }
        } else {
            if ($this->isRequired()) {
                $attributes['aria-required'] = 'true';
            }

            if ($hasErrors) {
                $attributes['aria-invalid'] = 'true';
                $attributes['aria-errormessage'] = $this->getPrefixedId() . 'Error';
            }
        }

        if ($this->getDescription() !== null) {
            $describedBy[] = $this->getPrefixedId() . 'Description';
        }

        if ($describedBy !== []) {
            $attributes['aria-describedby'] = \implode(' ', $describedBy);
        }

        return $attributes;
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
    public function loadValueCallback(\Closure $callback): static
    {
        $this->loadValueCallback = $callback;

        return $this;
    }

    #[\Override]
    public function getLoadValueCallback(): ?\Closure
    {
        return $this->loadValueCallback;
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

        if ($loadValues) {
            if ($this->loadValueCallback !== null) {
                ($this->loadValueCallback)($object, $this);
            } elseif (isset($data[$this->getObjectProperty()])) {
                $this->value($data[$this->getObjectProperty()]);
            }
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
