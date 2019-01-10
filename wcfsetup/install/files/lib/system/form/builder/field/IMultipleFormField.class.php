<?php
namespace wcf\system\form\builder\field;

/**
 * Represents a form field that support selecting or setting multiple values.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	5.2
 */
interface IMultipleFormField {
	/**
	 * value to indicate that there is no maximum number of values to be selected
	 * or set
	 */
	const NO_MAXIMUM_MULTIPLES = -1;
	
	/**
	 * Returns `true` if multiple values can be selected or set and returns `false`
	 * otherwise.
	 * 
	 * Per default, fields do not allow multiple values.
	 * 
	 * @return	bool
	 */
	public function allowsMultiple();
	
	/**
	 * Returns the maximum number of values that can be selected or set.
	 * If there is no maximum number, `IMultipleFormField::NO_MAXIMUM_MULTIPLES`
	 * is returned.
	 * 
	 * @return	int	maximum number of values
	 */
	public function getMaximumMultiples();
	
	/**
	 * Returns the minimum number of values that can be selected or set.
	 * 
	 * Per default, there is no minimum number.
	 *
	 * @return	int	minimum number of values
	 */
	public function getMinimumMultiples();
	
	/**
	 * Sets the maximum number of values that can be selected or set and returns
	 * this field. To unset the maximum number, pass `IMultipleFormField::NO_MAXIMUM_MULTIPLES`.
	 * 
	 * @param	int		$maximum	maximum number of values
	 * @return	static				this field
	 * 
	 * @throws	\InvalidArgumentException	if the given maximum number of values is invalid
	 */
	public function maximumMultiples($maximum);
	
	/**
	 * Sets the minimum number of values that can be selected or set and returns
	 * this field.
	 *
	 * @param	int		$minimum	maximum number of values
	 * @return	static				this field
	 * 
	 * @throws	\InvalidArgumentException	if the given minimum number of values is invalid
	 */
	public function minimumMultiples($minimum);
	
	/**
	 * Sets whether multiple values can be selected or set and returns this field.
	 * 
	 * @param	bool		$multiple	determines if multiple values can be selected/set
	 * @return	static				this field
	 */
	public function multiple($multiple = true);
}
