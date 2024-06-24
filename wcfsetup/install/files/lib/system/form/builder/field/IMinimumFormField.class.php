<?php

namespace wcf\system\form\builder\field;

/**
 * Represents a form field that supports setting the minimum of the field value.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
interface IMinimumFormField extends IFormField
{
    /**
     * Returns the minimum of the values of this field or `null` if no minimum
     * has been set.
     *
     * @return  null|int|float
     */
    public function getMinimum();

    /**
     * Sets the minimum of the values of this field. If `null` is passed, the
     * minimum is removed.
     *
     * @param null|int|float $minimum minimum field value
     * @return  static              this field
     *
     * @throws  \InvalidArgumentException   if the given minimum is no number or otherwise invalid
     */
    public function minimum($minimum = null);
}
