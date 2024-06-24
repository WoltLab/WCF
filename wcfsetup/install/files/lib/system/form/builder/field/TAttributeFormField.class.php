<?php

namespace wcf\system\form\builder\field;

use wcf\system\form\builder\IFormNode;

/**
 * Provides default implementations of `IAttributeFormField` methods.
 *
 * @author      Matthias Schmidt
 * @copyright   2001-2020 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       5.4
 */
trait TAttributeFormField
{
    /**
     * @var string[]
     */
    protected $fieldAttributes = [];

    /**
     * @var string[]
     */
    protected static $interfaceToFieldAttributeMap = [
        IAutoFocusFormField::class => 'autofocus',
        IAutoCompleteFormField::class => 'autocomplete',
        IImmutableFormField::class => 'disabled',
        IInputModeFormField::class => 'inputmode',
        IMaximumFormField::class => 'max',
        IMaximumLengthFormField::class => 'maxlength',
        IMinimumFormField::class => 'min',
        IMinimumLengthFormField::class => 'minlength',
        IPatternFormField::class => 'pattern',
        IPlaceholderFormField::class => 'placeholder',
    ];

    /**
     * Returns the value of the additional attribute of the actual field element with the given name.
     *
     * @throws      \InvalidArgumentException       if the given attribute is invalid or no such attribute exists
     * @return      static                          this form field
     */
    public function getFieldAttribute(string $name)
    {
        if (!$this->hasFieldAttribute($name)) {
            throw new \InvalidArgumentException("Unknown attribute '{$name}' requested for field '{$this->getId()}'.");
        }

        return $this->fieldAttributes[$name];
    }

    /**
     * Returns all additional attributes of the actual field element.
     */
    public function getFieldAttributes(): array
    {
        return $this->fieldAttributes;
    }

    /**
     * Adds the given additional attribute to the actual field element and returns this field.
     *
     * @throws      \InvalidArgumentException       if the given attribute is invalid
     * @return      static                          this form field
     */
    public function fieldAttribute(string $name, ?string $value = null)
    {
        static::validateFieldAttribute($name);

        $this->fieldAttributes[$name] = $value;

        return $this;
    }

    /**
     * Returns `true` if an additional attribute of the actual field element with the given name exists and returns
     * false` otherwise.
     *
     * @throws      \InvalidArgumentException       if the given attribute is invalid
     */
    public function hasFieldAttribute(string $name): bool
    {
        static::validateFieldAttribute($name);

        return isset($this->fieldAttributes[$name]);
    }

    /**
     * Removes the given additional attribute of the actual field element and returns this field.
     *
     * If the actual field element does not have the given attribute, this method silently ignores that fact.
     *
     * @throws      \InvalidArgumentException       if the given attribute is invalid
     * @return      static                          this form field
     */
    public function removeFieldAttribute(string $name)
    {
        static::validateFieldAttribute($name);

        unset($this->fieldAttributes[$name]);

        return $this;
    }

    /**
     * Returns a list of attributes that are not accessible via the field attribute methods.
     *
     * @return      string[]
     */
    protected static function getReservedFieldAttributes(): array
    {
        $attributes = [
            'class',
            'id',
            'required',
        ];

        foreach (static::$interfaceToFieldAttributeMap as $interface => $attribute) {
            if (\is_subclass_of(static::class, $interface)) {
                $attributes[] = $attribute;
            }
        }

        return $attributes;
    }

    /**
     * Checks if the given name is valid attribute name.
     *
     * @param string $name checked argument name
     *
     * @throws      \InvalidArgumentException       if the given attribute name is invalid
     * @see         IFormNode::validateAttribute()
     */
    abstract public static function validateAttribute($name);

    /**
     * Checks if the given name is a valid additional attribute name.
     *
     * @throws      \InvalidArgumentException       if the given additional attribute name is invalid
     */
    public static function validateFieldAttribute(string $name)
    {
        static::validateAttribute($name);

        if (\in_array(\strtolower($name), static::getReservedFieldAttributes())) {
            throw new \InvalidArgumentException("Attribute '{$name}' is not accessible as a field attribute.");
        }
    }
}
