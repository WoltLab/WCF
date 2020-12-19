<?php
namespace wcf\system\form\builder\field;

/**
 * Represents a form field that supports the `autocomplete` attribute.
 * 
 * @author      Matthias Schmidt
 * @copyright   2001-2020 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Form\Builder\Field
 * @see         https://html.spec.whatwg.org/multipage/form-control-infrastructure.html#autofilling-form-controls:-the-autocomplete-attribute
 * @since       5.4
 */
interface IAutoCompleteFormField {
	/**
	 * Sets the `autocomplete` attribute of the form field.
	 *
	 * Multiple tokens can be separated by spaces and if `null` is given, the attribute is unset.
	 *
	 * @throws      \InvalidArgumentException       if an invalid `autocomplete` token is included in the attribute value
	 */
	public function autoComplete(?string $autocomplete): self;
	
	/**
	 * Returns the `autocomplete` attribute of the form field.
	 *
	 * If `null` is returned, no `autocomplete` attribute will be set.
	 */
	public function getAutoComplete(): ?string;
}
