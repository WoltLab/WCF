<?php

namespace wcf\system\form\builder\field;

/**
 * Represents a form fields that supports additional attributes for the actual form element.
 *
 * "additional" refers to existing attributes, like `class`, which can already be set via dedicated methods.
 * The values of such attributes cannot be accessed or set with this API but only "additional" attributes for which
 * no dedicated methods exist.
 * If a class implementing this interface should add dedicated methods for certain attributes in the future, the methods
 * of this interface should forwards calls to these dedicated methods.
 *
 * @author      Matthias Schmidt
 * @copyright   2001-2020 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Form\Builder\Field
 * @since       5.4
 */
interface IAttributeFormField extends IFormField
{
    /**
     * Returns the value of the additional attribute of the actual field element with the given name.
     *
     * @throws      \InvalidArgumentException       if the given attribute is invalid or no such attribute exists
     */
    public function getFieldAttribute(string $name): self;

    /**
     * Returns all additional attributes of the actual field element.
     */
    public function getFieldAttributes(): array;

    /**
     * Adds the given additional attribute to the actual field element and returns this field.
     *
     * @throws      \InvalidArgumentException       if the given attribute is invalid
     */
    public function fieldAttribute(string $name, ?string $value = null): self;

    /**
     * Returns `true` if an additional attribute of the actual field element with the given name exists and returns
     * false` otherwise.
     *
     * @throws      \InvalidArgumentException       if the given attribute is invalid
     */
    public function hasFieldAttribute(string $name): bool;

    /**
     * Removes the given additional attribute of the actual field element and returns this field.
     *
     * If the actual field element does not have the given attribute, this method silently ignores that fact.
     *
     * @throws      \InvalidArgumentException       if the given attribute is invalid
     */
    public function removeFieldAttribute(string $name): self;

    /**
     * Checks if the given name is a valid additional attribute name.
     *
     * @throws      \InvalidArgumentException       if the given additional attribute name is invalid
     */
    public static function validateFieldAttribute(string $name);
}
