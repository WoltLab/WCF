<?php
namespace wcf\system\form\builder\field;

/**
 * Represents a form field that can be set as immutable.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	5.2
 */
interface IImmutableFormField {
	/**
	 * Sets whether the value of this field is immutable and returns this field.
	 * 
	 * @param	bool		$immutable	determines if field value is immutable
	 * @return	static				this field
	 */
	public function immutable($immutable = true);
	
	/**
	 * Returns `true` if the value of this field is immutable and returns `false`
	 * otherwise. By default, fields are mutable.
	 * 
	 * @return	bool
	 */
	public function isImmutable();
}
