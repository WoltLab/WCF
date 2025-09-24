<?php

namespace wcf\system\form\builder\field;

/**
 * Provides default implementations of `IMinimumFormField` methods.
 *
 * @author      Matthias Schmidt
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       5.2
 */
trait TMinimumFormField
{
    /**
     * minimum of the field value
     */
    protected null|int|float $minimum = null;

    /**
     * Returns the minimum of the values of this field or `null` if no minimum
     * has been set.
     */
    public function getMinimum(): null|int|float
    {
        return $this->minimum;
    }

    /**
     * Sets the minimum of the values of this field. If `null` is passed, the
     * minimum is removed.
     *
     * @throws  \InvalidArgumentException   if the given minimum is no number or otherwise invalid
     */
    public function minimum(null|int|float $minimum = null): static
    {
        if ($minimum !== null) {
            if ($this instanceof IMaximumFormField) {
                $maximum = $this->getMaximum();
                if ($maximum !== null && $minimum > $maximum) {
                    throw new \InvalidArgumentException(
                        "Minimum ({$minimum}) cannot be greater than maximum ({$maximum}) for field '{$this->getId()}'."
                    );
                }
            }
        }

        $this->minimum = $minimum;

        return $this;
    }
}
