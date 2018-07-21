<?php
namespace wcf\system\form\builder\field;

/**
 * Represents a form field that support `null` as its value.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	3.2
 */
interface INullableFormField {
	/**
	 * Returns `true` if this field supports `null` as its value and returns `false`
	 * otherwise.
	 * 
	 * Per default, fields do not support `null` as their value.
	 * 
	 * @return	bool
	 */
	public function isNullable();
	
	/**
	 * Sets whether this field supports `null` as its value and returns this field.
	 * 
	 * @param	bool	$nullable		determines if field supports `null` as its value
	 * @return	static				this node
	 */
	public function nullable(bool $nullable = true);
}
