<?php
declare(strict_types=1);
namespace wcf\system\form\builder\field;
use wcf\data\DatabaseObjectList;

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
	public function getOptions(): array;
	
	/**
	 * Sets the possible options of this field and returns this field.
	 * 
	 * Note: If PHP considers the key of the first selectable option to be empty
	 * and the this field is nullable, then the save value of that key is `null`
	 * instead of the given empty value.
	 * 
	 * If a `callable` is passed, it is expected that it either returns an array
	 * or a `DatabaseObjectList` object.
	 * 
	 * If a `DatabaseObjectList` object is passed and `$options->objectIDs === null`,
	 * `$options->readObjects()` is called so that the `readObjects()` does not have
	 * to be called by the API user.
	 * 
	 * @param	array|callable|DatabaseObjectList	$options	selectable options or callable returning the options
	 * @return	static					this field
	 * 
	 * @throws	\InvalidArgumentException		if given options are no array or callable or otherwise invalid
	 * @throws	\UnexpectedValueException		if callable does not return an array
	 */
	public function options($options): ISelectionFormField;
}
