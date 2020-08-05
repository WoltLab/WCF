<?php
namespace wcf\system\form\builder\field;

/**
 * Implementation of a form field for a hidden input field that is not visible in the rendered form.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	5.2
 */
class HiddenFormField extends AbstractFormField {
	/**
	 * @inheritDoc
	 */
	protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/Value';
	
	/**
	 * @inheritDoc
	 */
	protected $templateName = '__hiddenFormField';
	
	/**
	 * @inheritDoc
	 */
	public function readValue() {
		if ($this->getDocument()->hasRequestData($this->getPrefixedId())) {
			$this->value = $this->getDocument()->getRequestData($this->getPrefixedId());
		}
		
		return $this;
	}
}
