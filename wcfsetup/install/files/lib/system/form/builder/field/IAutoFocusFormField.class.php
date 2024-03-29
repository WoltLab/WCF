<?php

namespace wcf\system\form\builder\field;

/**
 * Represents a form field that can be auto-focused.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
interface IAutoFocusFormField extends IFormField
{
    /**
     * Sets whether this field is auto-focused and returns this field.
     *
     * @param bool $autoFocus determines if field is auto-focused
     * @return  static              this field
     */
    public function autoFocus($autoFocus = true);

    /**
     * Returns `true` if this field is auto-focused and returns `false` otherwise.
     *
     * By default, fields are not auto-focused.
     *
     * @return  bool
     */
    public function isAutoFocused();
}
