<?php

namespace wcf\system\form\builder\field;

use wcf\data\IStorableObject;
use wcf\system\form\builder\field\dependency\IFormFieldDependency;
use wcf\system\form\builder\field\validation\IFormFieldValidationError;
use wcf\system\form\builder\field\validation\IFormFieldValidator;
use wcf\system\form\builder\IFormParentNode;

/**
 * Decorator for form field objects.
 *
 * @author  Peter Lohse
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Form\Builder\Field
 * @since   5.4
 */
abstract class AbstractFormFieldDecorator implements IFormField
{
    /**
     * decorated field
     *
     * @var IFormField
     */
    protected $field;
    
    /**
     * Creates a decorator object for form fields
     *
     * @param  IFormField $field
     * @return void
     */
    public function __construct(IFormField $field)
    {
        $this->field = $field;
    }

    /**
     * @inheritDoc
     */
    public function addValidationError(IFormFieldValidationError $error)
    {
        $this->field->addValidationError($error);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addValidator(IFormFieldValidator $validator)
    {
        $this->field->addValidator($validator);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getFieldHtml()
    {
        return $this->field->getFieldHtml();
    }

    /**
     * @inheritDoc
     */
    public function getJavaScriptDataHandlerModule()
    {
        return $this->field->getJavaScriptDataHandlerModule();
    }

    /**
     * @inheritDoc
     */
    public function getObjectProperty()
    {
        return $this->field->getObjectProperty();
    }

    /**
     * @inheritDoc
     */
    public function getSaveValue()
    {
        return $this->field->getSaveValue();
    }

    /**
     * @inheritDoc
     */
    public function getValidationErrors()
    {
        return $this->field->getValidationErrors();
    }

    /**
     * @inheritDoc
     */
    public function getValidators()
    {
        return $this->field->getValidators();
    }

    /**
     * @inheritDoc
     */
    public function getValue()
    {
        return $this->field->getValue();
    }

    /**
     * @inheritDoc
     */
    public function hasValidator($validatorId)
    {
        return $this->field->hasValidator($validatorId);
    }

    /**
     * @inheritDoc
     */
    public function hasSaveValue()
    {
        return $this->field->hasSaveValue();
    }

    /**
     * @inheritDoc
     */
    public function isRequired()
    {
        return $this->field->isRequired();
    }

    /**
     * @inheritDoc
     */
    public function updatedObject(array $data, IStorableObject $object, $loadValues = true)
    {
        $this->field->updatedObject($data, $object, $loadValues);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function objectProperty($objectProperty)
    {
        $this->field->objectProperty($objectProperty);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function readValue()
    {
        $this->field->readValue();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function removeValidator($validatorId)
    {
        $this->field->removeValidator($validatorId);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function required($required = true)
    {
        $this->field->required($required);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function value($value)
    {
        $this->field->value($value);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return $this->field->getParent();
    }

    /**
     * @inheritDoc
     */
    public function parent(IFormParentNode $parentNode)
    {
        $this->field->parent($parentNode);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addClass($class)
    {
        $this->field->addClass($class);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addClasses(array $classes)
    {
        $this->field->addClasses($classes);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addDependency(IFormFieldDependency $dependency)
    {
        $this->field->addDependency($dependency);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function attribute($name, $value = null)
    {
        $this->field->attribute($name, $value);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function available($available = true)
    {
        $this->field->available($available);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function cleanup()
    {
        $this->field->cleanup();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function checkDependencies()
    {
        return $this->field->checkDependencies();
    }

    /**
     * @inheritDoc
     */
    public function getAttribute($name)
    {
        return $this->field->getAttribute($name);
    }

    /**
     * @inheritDoc
     */
    public function getAttributes()
    {
        return $this->field->getAttributes();
    }

    /**
     * @inheritDoc
     */
    public function getClasses()
    {
        return $this->field->getClasses();
    }

    /**
     * @inheritDoc
     */
    public function getDependencies()
    {
        return $this->field->getDependencies();
    }

    /**
     * @inheritDoc
     */
    public function getDocument()
    {
        return $this->field->getDocument();
    }

    /**
     * @inheritDoc
     */
    public function getHtml()
    {
        return $this->field->getHtml();
    }

    /**
     * @inheritDoc
     */
    public function getHtmlVariables()
    {
        return $this->field->getHtmlVariables();
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return $this->field->getId();
    }

    /**
     * @inheritDoc
     */
    public function getPrefixedId()
    {
        return $this->field->getPrefixedId();
    }

    /**
     * @inheritDoc
     */
    public function hasAttribute($name)
    {
        return $this->field->hasAttribute($name);
    }

    /**
     * @inheritDoc
     */
    public function hasClass($class)
    {
        return $this->field->hasClass($class);
    }

    /**
     * @inheritDoc
     */
    public function hasDependency($dependencyId)
    {
        return $this->field->hasDependency($dependencyId);
    }

    /**
     * @inheritDoc
     */
    public function id($id)
    {
        $this->field->id($id);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isAvailable()
    {
        return $this->field->isAvailable();
    }

    /**
     * @inheritDoc
     */
    public function populate()
    {
        $this->field->populate();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function removeAttribute($name)
    {
        $this->field->removeAttribute($name);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function removeClass($class)
    {
        $this->field->removeClass($class);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function removeDependency($dependencyId)
    {
        $this->field->removeDependency($dependencyId);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        $this->field->validate();
    }

    /**
     * @inheritDoc
     */
    public static function create($id)
    {
        throw new \BadMethodCallException('This method is available on a decorator.');
    }

    /**
     * @inheritDoc
     */
    public static function validateAttribute($name)
    {
        throw new \BadMethodCallException('This method is available on a decorator.');
    }

    /**
     * @inheritDoc
     */
    public static function validateClass($class)
    {
        throw new \BadMethodCallException('This method is available on a decorator.');
    }

    /**
     * @inheritDoc
     */
    public static function validateId($id)
    {
        throw new \BadMethodCallException('This method is available on a decorator.');
    }

    /**
     * @inheritDoc
     */
    public function description($languageItem = null, array $variables = [])
    {
        $this->field->description($languageItem, $variables);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getDescription()
    {
        return $this->field->getDescription();
    }

    /**
     * @inheritDoc
     */
    public function getLabel()
    {
        return $this->field->getLabel();
    }

    /**
     * @inheritDoc
     */
    public function label($languageItem = null, array $variables = [])
    {
        $this->field->label($languageItem, $variables);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function requiresLabel()
    {
        return $this->field->requiresLabel();
    }
}
