<?php
namespace wcf\system\form\builder\field\user;
use wcf\data\user\UserProfile;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\IMultipleFormField;
use wcf\system\form\builder\field\INullableFormField;
use wcf\system\form\builder\field\TMultipleFormField;
use wcf\system\form\builder\field\TNullableFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;

/**
 * Implementation of a form field to enter existing users.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field\User
 * @since	5.2
 */
class UserFormField extends AbstractFormField implements IMultipleFormField, INullableFormField {
	use TMultipleFormField;
	use TNullableFormField;
	
	/**
	 * @inheritDoc
	 */
	protected $templateName = '__userFormField';
	
	/**
	 * @inheritDoc
	 */
	public function readValue() {
		if ($this->getDocument()->hasRequestData($this->getPrefixedId())) {
			$value = $this->getDocument()->getRequestData($this->getPrefixedId());
			
			if (is_string($value)) {
				if ($this->allowsMultiple()) {
					$this->__value = ArrayUtil::trim(explode(',', $value));
				}
				else {
					$this->__value = StringUtil::trim($value);
				}
			}
		}
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		if ($this->isRequired() && ($this->getValue() === null || $this->getValue() === '') || (is_array($this->getValue()) && empty($this->getValue()))) {
			$this->addValidationError(new FormFieldValidationError('empty'));
		}
		else if (!$this->isRequired()) {
			if ($this->allowsMultiple()) {
				if ($this->getMinimumMultiples() > 0 && count($this->getValue()) < $this->getMinimumMultiples()) {
					$this->addValidationError(new FormFieldValidationError(
						'minimumMultiples',
						'wcf.form.field.user.error.minimumMultiples',
						[
							'minimumCount' => $this->getMinimumMultiples(),
							'count' => count($this->getValue())
						]
					));
				}
				else if ($this->getMaximumMultiples() !== IMultipleFormField::NO_MAXIMUM_MULTIPLES && count($this->getValue()) > $this->getMaximumMultiples()) {
					$this->addValidationError(new FormFieldValidationError(
						'maximumMultiples',
						'wcf.form.field.user.error.maximumMultiples',
						[
							'maximumCount' => $this->getMaximumMultiples(),
							'count' => count($this->getValue())
						]
					));
				}
				else {
					// validate users
					$users = UserProfile::getUserProfilesByUsername($this->getValue());
					
					$nonExistentUsernames = [];
					foreach ($this->getValue() as $username) {
						if (!isset($users[$username])) {
							$nonExistentUsernames[] = $username;
						}
					}
					
					if (!empty($nonExistentUsernames)) {
						$this->addValidationError(new FormFieldValidationError(
							'nonExistent',
							'wcf.form.field.user.error.nonExistent',
							['nonExistentUsernames' => $nonExistentUsernames]
						));
					}
				}
			}
			else if ($this->getValue() !== '' && UserProfile::getUserProfileByUsername($this->getValue()) === null) {
				$this->addValidationError(new FormFieldValidationError(
					'nonExistent',
					'wcf.form.field.user.error.invalid'
				));
			}
		}
		
		parent::validate();
	}
}
