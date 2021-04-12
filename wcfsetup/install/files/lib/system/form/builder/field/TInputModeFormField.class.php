<?php

namespace wcf\system\form\builder\field;

/**
 * Provides default implementations of `IInputModeFormField` methods.
 *
 * @author      Matthias Schmidt
 * @copyright   2001-2020 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Form\Builder\Field
 * @since       5.4
 */
trait TInputModeFormField
{
    /** @var ?string */
    protected $inputMode;

    /**
     * Returns the `inputmode` attribute of the form field.
     *
     * If `null` is returned, no `inputmode` attribute will be set.
     */
    public function getInputMode(): ?string
    {
        return $this->inputMode;
    }

    /**
     * Sets the `inputmode` attribute of the form field.
     *
     * If `null` is given, the attribute is unset.
     *
     * @throws      \InvalidArgumentException       if an invalid `inputmode` token is included in the attribute value
     * @return      static                          this form field
     */
    public function inputMode(?string $inputMode)
    {
        if ($inputMode !== null && $inputMode !== 'none' && !\in_array($inputMode, $this->getValidInputModes())) {
            throw new \InvalidArgumentException("Invalid inputmode attribute '{$inputMode}'.");
        }

        $this->inputMode = $inputMode;

        return $this;
    }

    /**
     * Returns all valid `inputmode` tokens.
     */
    protected function getValidInputModes(): array
    {
        return [];
    }
}
