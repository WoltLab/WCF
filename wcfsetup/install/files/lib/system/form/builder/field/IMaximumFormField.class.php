<?php
namespace wcf\system\form\builder\field;

/**
 * Represents a form field that supports setting the maximum of the field value.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	5.2
 */
interface IMaximumFormField {
	/**
	 * Returns the maximum of the values of this field or `null` if no maximum
	 * has been set.
	 * 
	 * @return	null|number
	 */
	public function getMaximum();
	
	/**
	 * Sets the maximum of the values of this field. If `null` is passed, the
	 * maximum is removed.
	 * 
	 * @param	null|number	$maximum	maximum field value
	 * @return	static				this field
	 * 
	 * @throws	\InvalidArgumentException	if the given maximum is no number or otherwise invalid
	 */
	public function maximum($maximum = null);
}
