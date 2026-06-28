<?php

namespace wcf\system\form\builder\field;

use wcf\data\IStorableObject;
use wcf\system\form\builder\field\dependency\IFormFieldDependency;
use wcf\system\form\builder\field\validation\IFormFieldValidationError;
use wcf\system\form\builder\field\validation\IFormFieldValidator;
use wcf\system\form\builder\IFormParentNode;

/**
 * Default implementation of a decorator for an `IFormField` that forwards all methods defined
 * in the interface to the implementation of the decorated field.
 *
 * @author  Peter Lohse
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.4
 */
abstract class AbstractFormFieldDecorator implements IFormField
{
    /**
     * @var IFormField
     */
    protected $field;

    public function __construct(IFormField $field)
    {
        $this->field = $field;
    }

    #[\Override]
    public function addValidationError(IFormFieldValidationError $error)
    {
        $this->field->addValidationError($error);

        return $this;
    }

    #[\Override]
    public function addValidator(IFormFieldValidator $validator)
    {
        $this->field->addValidator($validator);

        return $this;
    }

    #[\Override]
    public function getFieldHtml()
    {
        return $this->field->getFieldHtml();
    }

    #[\Override]
    public function getJavaScriptDataHandlerModule()
    {
        return $this->field->getJavaScriptDataHandlerModule();
    }

    #[\Override]
    public function getObjectProperty()
    {
        return $this->field->getObjectProperty();
    }

    #[\Override]
    public function getSaveValue()
    {
        return $this->field->getSaveValue();
    }

    #[\Override]
    public function getValidationErrors()
    {
        return $this->field->getValidationErrors();
    }

    #[\Override]
    public function getValidators()
    {
        return $this->field->getValidators();
    }

    #[\Override]
    public function getValue()
    {
        return $this->field->getValue();
    }

    #[\Override]
    public function saveValueCallback(\Closure $callback): static
    {
        $this->field->saveValueCallback($callback);

        return $this;
    }

    #[\Override]
    public function getSaveValueCallback(): ?\Closure
    {
        return $this->field->getSaveValueCallback();
    }

    #[\Override]
    public function loadValueCallback(\Closure $callback): static
    {
        $this->field->loadValueCallback($callback);

        return $this;
    }

    #[\Override]
    public function getLoadValueCallback(): ?\Closure
    {
        return $this->field->getLoadValueCallback();
    }

    #[\Override]
    public function hasValidator(string $validatorId)
    {
        return $this->field->hasValidator($validatorId);
    }

    #[\Override]
    public function hasSaveValue()
    {
        return $this->field->hasSaveValue();
    }

    #[\Override]
    public function isRequired()
    {
        return $this->field->isRequired();
    }

    #[\Override]
    public function updatedObject(array $data, IStorableObject $object, bool $loadValues = true)
    {
        $this->field->updatedObject($data, $object, $loadValues);

        return $this;
    }

    #[\Override]
    public function objectProperty(string $objectProperty)
    {
        $this->field->objectProperty($objectProperty);

        return $this;
    }

    #[\Override]
    public function readValue()
    {
        $this->field->readValue();

        return $this;
    }

    #[\Override]
    public function removeValidator(string $validatorId)
    {
        $this->field->removeValidator($validatorId);

        return $this;
    }

    #[\Override]
    public function required(bool $required = true)
    {
        $this->field->required($required);

        return $this;
    }

    #[\Override]
    public function value(mixed $value)
    {
        $this->field->value($value);

        return $this;
    }

    #[\Override]
    public function getParent()
    {
        return $this->field->getParent();
    }

    #[\Override]
    public function parent(IFormParentNode $parentNode)
    {
        $this->field->parent($parentNode);

        return $this;
    }

    #[\Override]
    public function addClass(string $class): static
    {
        $this->field->addClass($class);

        return $this;
    }

    #[\Override]
    public function addClasses(array $classes): static
    {
        $this->field->addClasses($classes);

        return $this;
    }

    #[\Override]
    public function addDependency(IFormFieldDependency $dependency): static
    {
        $this->field->addDependency($dependency);

        return $this;
    }

    #[\Override]
    public function attribute(string $name, ?string $value = null): static
    {
        $this->field->attribute($name, $value);

        return $this;
    }

    #[\Override]
    public function available(bool $available = true): static
    {
        $this->field->available($available);

        return $this;
    }

    #[\Override]
    public function cleanup(): static
    {
        $this->field->cleanup();

        return $this;
    }

    #[\Override]
    public function checkDependencies(): bool
    {
        return $this->field->checkDependencies();
    }

    #[\Override]
    public function getAttribute(string $name): mixed
    {
        return $this->field->getAttribute($name);
    }

    #[\Override]
    public function getAttributes(): array
    {
        return $this->field->getAttributes();
    }

    #[\Override]
    public function getClasses(): array
    {
        return $this->field->getClasses();
    }

    #[\Override]
    public function getDependencies()
    {
        return $this->field->getDependencies();
    }

    #[\Override]
    public function getDocument()
    {
        return $this->field->getDocument();
    }

    #[\Override]
    public function getHtml()
    {
        return $this->field->getHtml();
    }

    #[\Override]
    public function getHtmlVariables()
    {
        return $this->field->getHtmlVariables();
    }

    #[\Override]
    public function getId()
    {
        return $this->field->getId();
    }

    #[\Override]
    public function getPrefixedId()
    {
        return $this->field->getPrefixedId();
    }

    #[\Override]
    public function hasAttribute(string $name)
    {
        return $this->field->hasAttribute($name);
    }

    #[\Override]
    public function hasClass(string $class)
    {
        return $this->field->hasClass($class);
    }

    #[\Override]
    public function hasDependency(string $dependencyId)
    {
        return $this->field->hasDependency($dependencyId);
    }

    #[\Override]
    public function id(string $id)
    {
        $this->field->id($id);

        return $this;
    }

    #[\Override]
    public function isAvailable()
    {
        return $this->field->isAvailable();
    }

    #[\Override]
    public function populate()
    {
        $this->field->populate();

        return $this;
    }

    #[\Override]
    public function removeAttribute(string $name)
    {
        $this->field->removeAttribute($name);

        return $this;
    }

    #[\Override]
    public function removeClass(string $class)
    {
        $this->field->removeClass($class);

        return $this;
    }

    #[\Override]
    public function removeDependency(string $dependencyId)
    {
        $this->field->removeDependency($dependencyId);

        return $this;
    }

    #[\Override]
    public function validate()
    {
        $this->field->validate();
    }

    #[\Override]
    public function description(?string $languageItem = null, array $variables = [])
    {
        $this->field->description($languageItem, $variables);

        return $this;
    }

    #[\Override]
    public function getDescription()
    {
        return $this->field->getDescription();
    }

    #[\Override]
    public function getLabel()
    {
        return $this->field->getLabel();
    }

    #[\Override]
    public function label(?string $languageItem = null, array $variables = [])
    {
        $this->field->label($languageItem, $variables);

        return $this;
    }

    #[\Override]
    public function requiresLabel()
    {
        return $this->field->requiresLabel();
    }
}
