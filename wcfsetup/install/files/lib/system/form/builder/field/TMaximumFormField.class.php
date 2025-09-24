<?php

namespace wcf\system\form\builder\field;

/**
 * Provides default implementations of `IMaximumFormField` methods.
 *
 * @author      Matthias Schmidt
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       5.2
 */
trait TMaximumFormField
{
    /**
     * maximum of the field value
     */
    protected null|int|float $maximum = null;

    /**
     * Returns the maximum of the values of this field or `null` if no maximum
     * has been set.
     */
    public function getMaximum(): null|int|float
    {
        return $this->maximum;
    }

    /**
     * Sets the maximum of the values of this field. If `null` is passed, the
     * maximum is removed.
     *
     * @throws \InvalidArgumentException if the given maximum is no number or otherwise invalid
     */
    public function maximum(null|int|float $maximum = null): static
    {
        if ($maximum !== null) {
            if ($this instanceof IMinimumFormField) {
                $minimum = $this->getMinimum();
                if ($minimum !== null && $minimum > $maximum) {
                    throw new \InvalidArgumentException(
                        "Minimum ({$minimum}) cannot be greater than maximum ({$maximum}) for field '{$this->getId()}'."
                    );
                }
            }
        }

        $this->maximum = $maximum;

        return $this;
    }
}
