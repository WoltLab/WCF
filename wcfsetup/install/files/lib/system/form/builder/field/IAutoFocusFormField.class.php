<?php

namespace wcf\system\form\builder\field;

/**
 * Represents a form field that can be autofocused.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Form\Builder\Field
 * @since   5.2
 */
interface IAutoFocusFormField extends IFormField
{
    /**
     * Sets whether this field is autofocused and returns this field.
     *
     * @param bool $autoFocus determines if field is autofocused
     * @return  static              this field
     */
    public function autoFocus($autoFocus = true);

    /**
     * Returns `true` if this field is autofocused and returns `false` otherwise.
     *
     * By default, fields are not autofocused.
     *
     * @return  bool
     */
    public function isAutoFocused();
}
