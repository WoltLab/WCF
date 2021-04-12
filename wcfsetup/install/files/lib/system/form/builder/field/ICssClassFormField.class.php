<?php

namespace wcf\system\form\builder\field;

/**
 * Represents a form fields that supports CSS classes for the actual form element.
 *
 * @author      Matthias Schmidt
 * @copyright   2001-2020 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Form\Builder\Field
 * @since       5.4
 */
interface ICssClassFormField extends IFormField
{
    /**
     * Adds the given CSS class to the actual field element and returns this field.
     *
     * @throws      \InvalidArgumentException       if the given class is invalid
     * @return      static                          this form field
     */
    public function addFieldClass(string $class);

    /**
     * Adds the given CSS classes to the actual field element and returns this field.
     *
     * @throws      \InvalidArgumentException       if any of the given classes is invalid
     * @return      static                          this form field
     */
    public function addFieldClasses(array $classes);

    /**
     * Returns all CSS classes of the actual field element.
     */
    public function getFieldClasses(): array;

    /**
     * Returns `true` if a CSS class of the actual field element with the given name exists and
     * returns `false` otherwise.
     *
     * @throws      \InvalidArgumentException       if the given class is invalid
     */
    public function hasFieldClass(string $class): bool;

    /**
     * Removes the given CSS class of the actual field element and returns this field.
     *
     * If the actual field element does not have the given CSS class, this method silently ignores that fact.
     *
     * @throws      \InvalidArgumentException       if the given class is invalid
     * @return      static                          this form field
     */
    public function removeFieldClass(string $class);
}
