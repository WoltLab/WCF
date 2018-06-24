<?php
namespace wcf\system\form\builder\field;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\style\StyleHandler;

/**
 * Implementation of a form field for to select a FontAwesome icon.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	3.2
 */
class IconFormField extends AbstractFormField {
	/**
	 * @inheritDoc
	 */
	protected $templateName = '__iconFormField';
	
	/**
	 * `true` if the global icon-related JavaScript code has already been included
	 * and `false` otherwise
	 * @var	bool
	 */
	protected static $includeJavaScript = true;
	
	/**
	 * @inheritDoc
	 */
	public function getHtmlVariables(): array {
		$value = static::$includeJavaScript;
		if (static::$includeJavaScript) {
			static::$includeJavaScript = false;
		}
		
		return [
			'__iconFormFieldIncludeJavaScript' => $value
		];
	}
	
	/**
	 * @inheritDoc
	 */
	public function getSaveValue() {
		if ($this->getValue()) {
			return 'fa-' . $this->getValue();
		}
		
		return '';
	}
	
	/**
	 * @inheritDoc
	 */
	public function readValue(): IFormField {
		if ($this->getDocument()->hasRequestData($this->getPrefixedId())) {
			$this->__value = $this->getDocument()->getRequestData($this->getPrefixedId());
		}
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		parent::validate();
		
		if (!$this->getValue()) {
			if ($this->isRequired()) {
				$this->addValidationError(new FormFieldValidationError('empty'));
			}
		}
		else if (!in_array($this->getValue(), StyleHandler::getInstance()->getIcons())) {
			$this->addValidationError(new FormFieldValidationError(
				'invalidValue',
				'wcf.global.form.error.noValidSelection'
			));
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function value($value): IFormField {
		$value = preg_replace('~^fa-~', '', $value);
		
		return parent::value($value);
	}
}
