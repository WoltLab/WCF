<?php
namespace wcf\system\form\builder\field;

/**
 * Represents a form field that supports a placeholder value.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	5.2
 */
interface IPlaceholderFormField {
	/**
	 * Returns the placeholder value of this field or `null` if no placeholder has
	 * been set.
	 * 
	 * @return	null|string
	 */
	public function getPlaceholder();
	
	/**
	 * Sets the placeholder value of this field using the given language item
	 * and returns this element. If `null` is passed, the placeholder value is
	 * removed.
	 * 
	 * @param	null|string	$languageItem	language item containing the placeholder or `null` to unset placeholder
	 * @param	array		$variables	additional variables used when resolving the language item
	 * @return	static				this field
	 * 
	 * @throws	\InvalidArgumentException	if the given value is invalid
	 */
	public function placeholder($languageItem = null, array $variables = []);
}
