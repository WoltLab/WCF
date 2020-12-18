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
	protected $templateName = '__checkboxFormField';
	
	/**
	 * @inheritDoc
	 */
	public function getHtml() {
		return WCF::getTPL()->fetch(
			$this->templateName,
			'wcf',
			[
				'field' => $this,
			]
		);
	}
}
