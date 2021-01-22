<?php

namespace wcf\system\form\builder\field;

/**
 * Provides default implementations of `IAutoCompleteFormField` methods.
 *
 * @author      Matthias Schmidt
 * @copyright   2001-2020 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Form\Builder\Field
 * @since       5.4
 */
trait TAutoCompleteFormField
{
    /** @var ?string */
    protected $autoComplete;

    /**
     * Sets the `autocomplete` attribute of the form field.
     *
     * Multiple tokens can be separated by spaces and if `null` is given, the attribute is unset.
     *
     * @throws      \InvalidArgumentException       if an invalid `autocomplete` token is included in the attribute value
     */
    public function autocomplete(?string $autoComplete): self
    {
        if ($autoComplete !== null && $autoComplete !== 'on' && $autoComplete !== 'off') {
            if (\preg_match('~^(?:section-\w+ )?(?:(shipping|billing) )?(?P<token>.+)$~', $autoComplete, $matches)) {
                if (!\in_array($matches['token'], $this->getValidAutoCompleteTokens())) {
                    throw new \InvalidArgumentException("Invalid autocomplete attribute '{$autoComplete}'.");
                }
            } else {
                throw new \InvalidArgumentException("Invalid autocomplete attribute '{$autoComplete}'.");
            }
        }

        $this->autoComplete = $autoComplete;

        return $this;
    }

    /**
     * Returns the `autocomplete` attribute of the form field.
     *
     * If `null` is returned, no `autocomplete` attribute will be set.
     */
    public function getAutoComplete(): ?string
    {
        return $this->autoComplete;
    }

    /**
     * Returns all valid `autocomplete` tokens.
     *
     * @see https://html.spec.whatwg.org/multipage/form-control-infrastructure.html#inappropriate-for-the-control
     */
    protected function getValidAutoCompleteTokens(): array
    {
        return [];
    }
}
