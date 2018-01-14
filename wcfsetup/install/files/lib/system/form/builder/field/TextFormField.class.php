<?php
namespace wcf\system\form\builder\field;
use wcf\system\form\builder\field\validation\FormFieldValidationError;

/**
 * Implementation of a form field for single-line text values.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	3.2
 */
class TextFormField extends AbstractFormField implements II18nFormField, IMaximumLengthFormField, IMinimumLengthFormField, IPlaceholderFormField {
	use TI18nFormField {
		validate as protected i18nValidate;
	}
	use TMaximumLengthFormField;
	use TMinimumLengthFormField;
	use TPlaceholderFormField;
	
	/**
	 * @inheritDoc
	 */
	protected $templateName = '__textFormField';
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		if ($this->isI18n()) {
			$this->i18nValidate();
			
			$value = $this->getValue();
			if ($this->hasPlainValue()) {
				$this->validateText($value);
			}
			else {
				foreach ($value as $languageID => $languageValue) {
					$this->validateText($languageValue, $languageID);
				}
			}
		}
		else {
			if ($this->isRequired() && $this->getValue() === '') {
				$this->addValidationError(new FormFieldValidationError('empty'));
			}
			else {
				$this->validateText($this->getValue());
			}
		}
		
		parent::validate();
	}
	
	/**
	 * Checks the length of the given text with the given language.
	 * 
	 * @param	string		$text		validated text
	 * @param	null|int	$languageID	language id of validated text or `null` for monolingual text
	 */
	protected function validateText($text, $languageID = null) {
		if ($this->getMinimumLength() !== null && mb_strlen($text) < $this->getMinimumLength()) {
			$this->addValidationError(new FormFieldValidationError('minimumLength', 'wcf.global.form.text.error.minimumLength', [
				'languageID' => $languageID,
				'length' => mb_strlen($text),
				'minimumLength' => $this->getMinimumLength()
			]));
		}
		else if ($this->getMaximumLength() !== null && mb_strlen($text) > $this->getMaximumLength()) {
			$this->addValidationError(new FormFieldValidationError('maximumLength', 'wcf.global.form.text.error.maximumLength', [
				'languageID' => $languageID,
				'length' => mb_strlen($text),
				'maximumLength' => $this->getMaximumLength()
			]));
		}
	}
}
