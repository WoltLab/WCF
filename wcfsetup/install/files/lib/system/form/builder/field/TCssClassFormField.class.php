<?php

namespace wcf\system\form\builder\field;

/**
 * Provides default implementations of `ICssClassFormField` methods.
 *
 * @author      Matthias Schmidt
 * @copyright   2001-2020 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Form\Builder\Field
 * @since       5.4
 */
trait TCssClassFormField
{
    /**
     * CSS classes of this node
     * @var string[]
     */
    protected $fieldClasses = [];

    /**
     * Adds the given CSS class to the actual field element and returns this field.
     *
     * @throws      \InvalidArgumentException       if the given class is invalid
     */
    public function addFieldClass(string $class): self
    {
        static::validateClass($class);

        if (!\in_array($class, $this->fieldClasses)) {
            $this->fieldClasses[] = $class;
        }

        return $this;
    }

    /**
     * Adds the given CSS classes to the actual field element and returns this field.
     *
     * @throws      \InvalidArgumentException       if any of the given classes is invalid
     */
    public function addFieldClasses(array $classes): self
    {
        foreach ($classes as $class) {
            $this->addFieldClass($class);
        }

        return $this;
    }

    /**
     * Returns all CSS classes of the actual field element.
     */
    public function getFieldClasses(): array
    {
        return $this->fieldClasses;
    }

    /**
     * Returns `true` if a CSS class of the actual field element with the given name exists and
     * returns `false` otherwise.
     *
     * @throws      \InvalidArgumentException       if the given class is invalid
     */
    public function hasFieldClass(string $class): bool
    {
        static::validateClass($class);

        return \array_search($class, $this->fieldClasses) !== false;
    }

    /**
     * Removes the given CSS class of the actual field element and returns this field.
     *
     * If the actual field element does not have the given CSS class, this method silently ignores that fact.
     *
     * @throws      \InvalidArgumentException       if the given class is invalid
     */
    public function removeFieldClass(string $class): self
    {
        static::validateClass($class);

        $index = \array_search($class, $this->fieldClasses);
        if ($index !== false) {
            unset($this->fieldClasses[$index]);
        }

        return $this;
    }

    /**
     * Checks if the given parameter class is a valid CSS class.
     *
     * @param       string          $class          checked class
     *
     * @throws      \InvalidArgumentException       if the given class is invalid
     */
    abstract public static function validateClass($class);
}
