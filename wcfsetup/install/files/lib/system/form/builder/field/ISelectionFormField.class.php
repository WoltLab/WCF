<?php
namespace wcf\system\form\builder\field;

/**
 * Represents a form field that consists of a predefined set of possible values.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	3.2
 */
interface ISelectionFormField {
	/**
	 * Returns the possible options of this field.
	 * 
	 * @return	array
	 * 
	 * @throws	\BadMethodCallException		if no options have been set
	 */
	public function getOptions();
	
	/**
	 * Sets the possible options of this field and returns this field.
	 * 
	 * @param	array|callable		$options	selectable options or callable returning the options
	 * @return	static					this field
	 * 
	 * @throws	\InvalidArgumentException		if given options are no array or callable or otherwise invalid
	 * @throws	\UnexpectedValueException		if callable does not return an array
	 */
	public function options($options);
}
