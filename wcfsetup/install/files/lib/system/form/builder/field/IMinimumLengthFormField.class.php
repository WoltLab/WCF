<?php
namespace wcf\system\form\builder\field;
use wcf\data\language\Language;

/**
 * Represents a form field that supports setting the minimum length of the field value.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	3.2
 */
interface IMinimumLengthFormField {
	/**
	 * Returns the minimum length of the values of this field or `null` if no minimum
	 * length has been set.
	 * 
	 * @return	null|int
	 */
	public function getMinimumLength();
	
	/**
	 * Sets the minimum length of the values of this field. If `null` is passed, the
	 * minimum length is removed.
	 * 
	 * @param	null|int	$minimumLength	minimum field value length
	 * @return	static				this field
	 * 
	 * @throws	\InvalidArgumentException	if the given minimum length is no integer or otherwise invalid
	 */
	public function minimumLength($minimumLength = null);
	
	/**
	 * Validates the minimum length of the given text.
	 * 
	 * @param	string		$text		validated text
	 * @param	null|Language	$language	language of the validated text
	 */
	public function validateMinimumLength($text, Language $language = null);
}
