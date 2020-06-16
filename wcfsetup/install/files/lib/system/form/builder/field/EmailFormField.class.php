<?php
namespace wcf\system\form\builder\field;
use wcf\data\language\Language;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\language\LanguageFactory;
use wcf\util\UserUtil;

/**
 * Implementation of a form field for an email address.
 * 
 * The default label of fields of this class is `wcf.form.field.email`.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	5.2
 */
class EmailFormField extends AbstractFormField implements IAutoFocusFormField, II18nFormField, IImmutableFormField, IPlaceholderFormField {
	use TAutoFocusFormField;
	use TImmutableFormField;
	use TI18nFormField {
		validate as protected i18nValidate;
	}
	use TPlaceholderFormField;
	
	/**
	 * @inheritDoc
	 */
	protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/Value';
	
	/**
	 * @inheritDoc
	 */
	protected $templateName = '__emailFormField';
	
	/**
	 * @inheritDoc
	 */
	public function __construct() {
		$this->label('wcf.form.field.email');
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		if ($this->isI18n()) {
			$this->i18nValidate();
			
			if (empty($this->getValidationErrors())) {
				$value = $this->getValue();
				if ($this->hasPlainValue()) {
					$this->validateEmail($value);
				}
				else {
					foreach ($value as $languageID => $languageValue) {
						$this->validateEmail($languageValue, LanguageFactory::getInstance()->getLanguage($languageID));
					}
				}
			}
		}
		else {
			if ($this->isRequired() && ($this->getValue() === null || $this->getValue() === '')) {
				$this->addValidationError(new FormFieldValidationError('empty'));
			}
			else {
				$this->validateEmail($this->getValue());
			}
		}
		
		parent::validate();
	}
	
	/**
	 * Validates the given email address in the given language.
	 * 
	 * @param	string		$email		validated email address
	 * @param	null|Language	$language	language of validated email address or `null` for monolingual email address
	 */
	protected function validateEmail($email, Language $language = null) {
		if ($email === null || $email === '') {
			return;
		}
		
		if (!UserUtil::isValidEmail($email)) {
			$this->addValidationError(new FormFieldValidationError(
				'invalidEmail',
				'wcf.form.field.email.error.invalidEmail',
				['language' => $language]
			));
		}
	}
}
