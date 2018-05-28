<?php
namespace wcf\system\form\builder\field;

/**
 * Implementation of a form field for disabling an object.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	3.2
 */
class IsDisabledFormField extends BooleanFormField {
	use TForcedIdFormField;
	
	/**
	 * @inheritDoc
	 */
	protected static function getForcedId(): string {
		return 'isDisabled';
	}
}
