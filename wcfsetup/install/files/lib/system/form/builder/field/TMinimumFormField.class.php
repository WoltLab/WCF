<?php

namespace wcf\system\form\builder\field;

/**
 * Provides default implementations of `IMinimumFormField` methods.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
trait TMinimumFormField
{
    /**
     * minimum of the field value
     * @var null|int|float
     */
    protected $minimum;

    /**
     * Returns the minimum of the values of this field or `null` if no minimum
     * has been set.
     *
     * @return  null|int|float
     */
    public function getMinimum()
    {
        return $this->minimum;
    }

    /**
     * Sets the minimum of the values of this field. If `null` is passed, the
     * minimum is removed.
     *
     * @param null|int|float $minimum minimum field value
     * @return  static              this field
     *
     * @throws  \InvalidArgumentException   if the given minimum is no number or otherwise invalid
     */
    public function minimum($minimum = null)
    {
        if ($minimum !== null) {
            if (!\is_numeric($minimum)) {
                throw new \InvalidArgumentException(
                    "Given minimum is no int, '" . \gettype($minimum) . "' given for field '{$this->getId()}'."
                );
            }

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
