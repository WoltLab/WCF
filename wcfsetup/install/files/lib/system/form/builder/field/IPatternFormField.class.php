<?php

namespace wcf\system\form\builder\field;

/**
 * Represents a form field that supports the `pattern` attribute.
 *
 * @author      Matthias Schmidt
 * @copyright   2001-2020 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Form\Builder\Field
 * @see         https://html.spec.whatwg.org/multipage/input.html#attr-input-pattern
 * @since       5.4
 */
interface IPatternFormField
{
    /**
     * Returns the `pattern` attribute of the form field.
     *
     * If `null` is returned, no `pattern` attribute will be set.
     */
    public function getPattern(): ?string;

    /**
     * Sets the `pattern` attribute of the form field.
     *
     * If `null` is given, the attribute is unset.
     *
     * @return      static  this form field
     */
    public function pattern(?string $pattern);
}
