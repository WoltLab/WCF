<?php

namespace wcf\system\form\builder\field\dependency;

/**
 * Represents a dependency that requires the value of a field to be in the interval
 * [minimum, maximum].
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.5
 */
final class ValueIntervalFormFieldDependency extends AbstractFormFieldDependency
{
    /**
     * maximum value of the value interval
     */
    protected $maximum;

    /**
     * minimum value of the value interval
     */
    protected $minimum;

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_valueIntervalFormFieldDependency';

    /**
     * @inheritDoc
     */
    public function checkDependency()
    {
        $value = $this->getField()->getValue();
        if (!\is_numeric($value)) {
            return false;
        }

        $value = \floatval($value);

        if ($this->minimum !== null && $this->minimum > $value) {
            return false;
        } elseif ($this->maximum !== null && $this->maximum < $value) {
            return false;
        }

        return true;
    }

    /**
     * Returns the maximum value of the value interval or `null` if no maximum has been set.
     */
    public function getMaximum(): ?float
    {
        return $this->maximum;
    }

    /**
     * Returns the minimum value of the value interval or `null` if no minimum has been set.
     */
    public function getMinimum(): ?float
    {
        return $this->minimum;
    }

    /**
     * Sets the maximum value of the value interval or unsets the maximum value if `null` is given.
     */
    public function maximum(?float $maximum = null): self
    {
        if ($maximum !== null && $this->minimum !== null && $maximum < $this->minimum) {
            throw new \InvalidArgumentException(
                "Maximum value '{$maximum}' for dependency '{$this->getId()}' is less than set minimum value '{$this->minimum}'."
            );
        }

        $this->maximum = $maximum;

        return $this;
    }

    /**
     * Sets the minimum value of the value interval or unsets the minimum value if `null` is given.
     */
    public function minimum(?float $minimum = null): self
    {
        if ($minimum !== null && $this->maximum !== null && $minimum > $this->maximum) {
            throw new \InvalidArgumentException(
                "Minimum value '{$minimum}' for dependency '{$this->getId()}' is greater than set maximum value '{$this->maximum}'."
            );
        }

        $this->minimum = $minimum;

        return $this;
    }
}
