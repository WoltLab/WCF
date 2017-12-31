<?php
namespace wcf\system\form\builder\field;

/**
 * Represents a form field that supports i18n input.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	3.2
 */
interface II18nFormField extends IFormField {
	/**
	 * Sets whether this field is supports i18n input and returns this field.
	 * 
	 * @param	bool		$i18n		determined if field is supports i18n input
	 * @return	static				this field
	 * 
	 * @throws	\InvalidArgumentException	if the given value is no bool
	 */
	public function i18n($i18n = true);
	
	/**
	 * Returns `true` if this field supports i18n input and returns `false` otherwise.
	 * By default, fields do not support i18n input.
	 * 
	 * @return	bool
	 */
	public function isI18n();
}
