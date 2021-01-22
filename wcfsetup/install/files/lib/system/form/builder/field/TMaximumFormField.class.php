<?php

namespace wcf\system\form\builder\field;

/**
 * Provides default implementations of `IMaximumFormField` methods.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Form\Builder\Field
 * @since   5.2
 */
trait TMaximumFormField
{
    /**
     * maximum of the field value
     * @var null|number
     */
    protected $maximum;

    /**
     * Returns the maximum of the values of this field or `null` if no maximum
     * has been set.
     *
     * @return  null|number
     */
    public function getMaximum()
    {
        return $this->maximum;
    }

    /**
     * Sets the maximum of the values of this field. If `null` is passed, the
     * maximum is removed.
     *
     * @param   null|number $maximum    maximum field value
     * @return  static              this field
     *
     * @throws  \InvalidArgumentException   if the given maximum is no number or otherwise invalid
     */
    public function maximum($maximum = null)
    {
        if ($maximum !== null) {
            if (!\is_numeric($maximum)) {
                throw new \InvalidArgumentException("Given maximum is no int, '" . \gettype($maximum) . "' given.");
            }

            if ($this instanceof IMinimumFormField) {
                $minimum = $this->getMinimum();
                if ($minimum !== null && $minimum > $maximum) {
                    throw new \InvalidArgumentException(
                        "Minimum ({$minimum}) cannot be greater than maximum ({$maximum})."
                    );
                }
            }
        }

        $this->maximum = $maximum;

        return $this;
    }
}
