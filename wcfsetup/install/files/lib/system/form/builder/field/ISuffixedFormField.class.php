<?php
namespace wcf\system\form\builder\field;

/**
 * Represents a form field that supports displaying a suffix.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	5.2
 */
interface ISuffixedFormField {
	/**
	 * Returns the suffix of this field or `null` if no suffix has been set.
	 * 
	 * @return	null|string
	 */
	public function getSuffix();
	
	/**
	 * Sets the suffix of this field using the given language item and returns
	 * this element. If `null` is passed, the suffix is removed.
	 * 
	 * @param	null|string	$languageItem	language item containing the suffix or `null` to unset suffix
	 * @param	array		$variables	additional variables used when resolving the language item
	 * @return	static				this field
	 * 
	 * @throws	\InvalidArgumentException	if the given language item is invalid
	 */
	public function suffix($languageItem = null, array $variables = []);
}
