<?php
namespace wcf\system\form\builder\field;
use wcf\system\WCF;

/**
 * Implementation of a checkbox form field for boolen values.
 * 
 * @author	Peter Lohse
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	5.3
 */
class CheckboxFormField extends BooleanFormField {
	/**
	 * @inheritDoc
	 */
	public function getHtml() {
		if ($this->requiresLabel() && $this->getLabel() === null) {
			throw new \UnexpectedValueException("Form field '{$this->getPrefixedId()}' requires a label.");
		}
		
		return WCF::getTPL()->fetch(
			'__checkboxFormField',
			'wcf',
			[
				'field' => $this,
			]
		);
	}
}
