<?php
namespace wcf\system\form\builder\field\user;
use wcf\data\user\UserProfile;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\IAutoFocusFormField;
use wcf\system\form\builder\field\IImmutableFormField;
use wcf\system\form\builder\field\IMultipleFormField;
use wcf\system\form\builder\field\INullableFormField;
use wcf\system\form\builder\field\TAutoFocusFormField;
use wcf\system\form\builder\field\TImmutableFormField;
use wcf\system\form\builder\field\TMultipleFormField;
use wcf\system\form\builder\field\TNullableFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\IFormDocument;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;

/**
 * Implementation of a form field to enter existing users.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field\User
 * @since	5.2
 */
class UserFormField extends AbstractFormField implements IAutoFocusFormField, IImmutableFormField, IMultipleFormField, INullableFormField {
	use TAutoFocusFormField;
	use TImmutableFormField;
	use TMultipleFormField;
	use TNullableFormField;
	
	/**
	 * @inheritDoc
	 */
	protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/User';
	
	/**
	 * @inheritDoc
	 */
	protected $templateName = '__userFormField';
	
	/**
	 * user profiles of the entered users (and `null` for non-existing users; only relevant for
	 * invalid inputs)
	 * @var	UserProfile[]|null[]
	 */
	protected $users = [];
	
	/**
	 * Returns the user profiles of the entered users (and `null` for non-existing users; only
	 * relevant for invalid inputs).
	 * 
	 * @return	UserProfile[]|null[]
	 */
	public function getUsers() {
		return $this->users;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getSaveValue() {
		if (empty($this->getUsers())) {
			if ($this->isNullable()) {
				return null;
			}
			
			return 0;
		}
		
		return current($this->getUsers())->userID;
	}
	
	/**
	 * @inheritDoc
	 */
	public function populate() {
		parent::populate();
		
		if ($this->allowsMultiple()) {
			$this->getDocument()->getDataHandler()->addProcessor(new CustomFormDataProcessor('multipleUsers', function(IFormDocument $document, array $parameters) {
				if ($this->checkDependencies()) {
					$parameters[$this->getObjectProperty()] = array_values(array_map(function(UserProfile $user) {
						return $user->userID;
					}, $this->getUsers()));
				}
				
				return $parameters;
			}));
		}
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function readValue() {
		if ($this->getDocument()->hasRequestData($this->getPrefixedId())) {
			$this->users = [];
			
			$value = $this->getDocument()->getRequestData($this->getPrefixedId());
			
			if (is_string($value)) {
				if ($this->allowsMultiple()) {
					$this->value = ArrayUtil::trim(explode(',', $value));
				}
				else {
					$this->value = StringUtil::trim($value);
				}
			}
		}
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		if (
			$this->isRequired() && (
				($this->getValue() === null || $this->getValue() === '') ||
				(is_array($this->getValue()) && empty($this->getValue()))
			)
		) {
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
					$this->users = UserProfile::getUserProfilesByUsername($this->getValue());
					
					$nonExistentUsernames = [];
					foreach ($this->getValue() as $username) {
						if (!isset($this->users[$username])) {
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
			else if ($this->getValue() !== '') {
				$user = UserProfile::getUserProfileByUsername($this->getValue());
				
				if ($user === null) {
					$this->addValidationError(new FormFieldValidationError(
						'nonExistent',
						'wcf.form.field.user.error.invalid'
					));
				}
				else {
					$this->users[] = $user;
				}
			}
		}
		
		parent::validate();
	}
	
	/**
	 * @inheritDoc
	 */
	public function value($value) {
		// ensure array value for form fields that actually support multiple values;
		// allows enabling support for multiple values for existing fields
		if ($this->allowsMultiple() && !is_array($value)) {
			$value = [$value];
		}
		
		if ($this->allowsMultiple()) {
			$this->users = UserProfileRuntimeCache::getInstance()->getObjects($value);
			
			$value = array_map(function(UserProfile $user) {
				return $user->username;
			}, $this->users);
		}
		else {
			$user = UserProfileRuntimeCache::getInstance()->getObject($value);
			$this->users[] = $user;
			$value = $user->username;
		}
		
		return parent::value($value);
	}
}
