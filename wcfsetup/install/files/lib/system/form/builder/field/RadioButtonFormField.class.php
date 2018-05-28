<?php
namespace wcf\system\form\builder\field;

/**
 * Implementation of a radio buttons form field for selecting a single value.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	3.2
 */
class RadioButtonFormField extends AbstractFormField implements ISelectionFormField {
	use TSelectionFormField;
	
	/**
	 * @inheritDoc
	 */
	protected $templateName = '__radioButtonFormField';
	
	/**
	 * @inheritDoc
	 */
	protected static function supportsNestedOptions(): bool {
		return false;
	}
}
