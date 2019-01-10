<?php
namespace wcf\system\form\builder\field\user;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\IMaximumLengthFormField;
use wcf\system\form\builder\field\IMinimumLengthFormField;
use wcf\system\form\builder\field\INullableFormField;
use wcf\system\form\builder\field\IPlaceholderFormField;
use wcf\system\form\builder\field\TMaximumLengthFormField;
use wcf\system\form\builder\field\TMinimumLengthFormField;
use wcf\system\form\builder\field\TNullableFormField;
use wcf\system\form\builder\field\TPlaceholderFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\util\UserUtil;

/**
 * Implementation of a form field to enter one non-existing username.
 * 
 * Usernames have a minimum length of 3 characters and a maximum length of 100 characters by default.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field\User
 * @since	5.2
 */
class UsernameFormField extends AbstractFormField implements IMaximumLengthFormField, IMinimumLengthFormField, INullableFormField, IPlaceholderFormField {
	use TMaximumLengthFormField;
	use TMinimumLengthFormField;
	use TNullableFormField;
	use TPlaceholderFormField;
	
	/**
	 * @inheritDoc
	 */
	protected $templateName = '__usernameFormField';
	
	/**
	 * Creates a new instance of `UsernameFormField`.
	 */
	public function __construct() {
		$this->maximumLength(100);
		$this->minimumLength(3);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getSaveValue() {
		if ($this->getValue() === null && !$this->isNullable()) {
			return '';
		}
		
		return parent::getSaveValue();
	}
	
	/**
	 * @inheritDoc
	 */
	public function readValue() {
		if ($this->getDocument()->hasRequestData($this->getPrefixedId())) {
			$value = $this->getDocument()->getRequestData($this->getPrefixedId());
			
			if (is_string($value)) {
				$this->__value = $value;
			}
		}
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		if ($this->getValue() === '' || $this->getValue() === null) {
			if ($this->isRequired()) {
				$this->addValidationError(new FormFieldValidationError('empty'));
			}
		}
		else {
			$this->validateMinimumLength($this->getValue());
			$this->validateMaximumLength($this->getValue());
			
			if (empty($this->getValidationErrors())) {
				if (!UserUtil::isValidUsername($this->getValue())) {
					$this->addValidationError(new FormFieldValidationError(
						'invalid',
						'wcf.form.field.username.error.invalid'
					));
				}
				else if (!UserUtil::isAvailableUsername($this->getValue())) {
					$this->addValidationError(new FormFieldValidationError(
						'notUnique',
						'wcf.form.field.username.error.notUnique'
					));
				}
			}
		}
		
		parent::validate();
	}
}
