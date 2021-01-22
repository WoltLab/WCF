<?php

namespace wcf\system\form\builder\field;

/**
 * Represents a form field that supports the `inputmode` attribute.
 *
 * @author      Matthias Schmidt
 * @copyright   2001-2020 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Form\Builder\Field
 * @see         https://html.spec.whatwg.org/multipage/interaction.html#input-modalities:-the-inputmode-attribute
 * @since       5.4
 */
interface IInputModeFormField
{
    /**
     * Returns the `inputmode` attribute of the form field.
     *
     * If `null` is returned, no `inputmode` attribute will be set.
     */
    public function getInputMode(): ?string;

    /**
     * Sets the `inputmode` attribute of the form field.
     *
     * If `null` is given, the attribute is unset.
     *
     * @throws      \InvalidArgumentException       if an invalid `inputmode` token is included in the attribute value
     */
    public function inputMode(?string $inputMode): self;
}
