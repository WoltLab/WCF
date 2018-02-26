<?php
namespace wcf\system\form\builder\field;
use wcf\data\user\UserProfile;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;

/**
 * Implementation of a form field to enter existing users.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	3.2
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
	public function readValue(): IFormField {
		if (isset($_POST[$this->getPrefixedId()]) && is_string($_POST[$this->getPrefixedId()])) {
			if ($this->allowsMultiple()) {
				$this->__value = ArrayUtil::trim(explode(',', $_POST[$this->getPrefixedId()]));
			}
			else {
				$this->__value = StringUtil::trim($_POST[$this->getPrefixedId()]);
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
					$this->addValidationError(new FormFieldValidationError('minimumMultiples', 'wcf.form.field.user.error.minimumMultiples', [
						'minimumCount' => $this->getMinimumMultiples(),
						'userCount' => count($this->getValue())
					]));
				}
				else if ($this->getMaximumMultiples() !== IMultipleFormField::NO_MAXIMUM_MULTIPLES && count($this->getValue()) > $this->getMaximumMultiples()) {
					$this->addValidationError(new FormFieldValidationError('maximumMultiples', 'wcf.form.field.user.error.maximumMultiples', [
						'maximumCount' => $this->getMaximumMultiples(),
						'userCount' => count($this->getValue())
					]));
				}
				else {
					// validate users
					$users = UserProfile::getUserProfilesByUsername($this->getValue());
					
					$invalidUsernames = [];
					foreach ($this->getValue() as $username) {
						if (!isset($users[$username])) {
							$invalidUsernames[] = $username;
						}
					}
					
					if (!empty($invalidUsernames)) {
						$this->addValidationError(new FormFieldValidationError('invalid', 'wcf.form.field.user.error.invalid', [
							'invalidUsernames' => $invalidUsernames
						]));
					}
				}
			}
			else if ($this->getValue() !== '' && UserProfile::getUserProfileByUsername($this->getValue()) === null) {
				$this->addValidationError(new FormFieldValidationError('invalid', 'wcf.form.field.user.error.invalid'));
			}
		}
		
		parent::validate();
	}
}
