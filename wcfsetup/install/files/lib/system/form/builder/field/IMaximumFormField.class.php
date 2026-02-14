<?php

namespace wcf\system\form\builder\field;

/**
 * Represents a form field that supports setting the maximum of the field value.
 *
 * @author      Matthias Schmidt
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       5.2
 */
interface IMaximumFormField extends IFormField
{
    /**
     * Returns the maximum of the values of this field or `null` if no maximum
     * has been set.
     */
    public function getMaximum(): null|int|float;

    /**
     * Sets the maximum of the values of this field. If `null` is passed, the
     * maximum is removed.
     *
     * @throws  \InvalidArgumentException   if the given maximum is no number or otherwise invalid
     */
    public function maximum(null|int|float $maximum = null): static;
}
