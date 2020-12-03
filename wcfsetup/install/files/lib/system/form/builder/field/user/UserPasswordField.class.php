<?php
namespace wcf\system\form\builder\field\user;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\IAutoFocusFormField;
use wcf\system\form\builder\field\IPlaceholderFormField;
use wcf\system\form\builder\field\TAutoFocusFormField;
use wcf\system\form\builder\field\TDefaultIdFormField;
use wcf\system\form\builder\field\TPlaceholderFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\WCF;

/**
 * Implementation of a form field to enter the password of the active user.
 * 
 * This field is only available for logged-in users not user third party providers for logging in.
 * This field uses the `wcf.user.password` language item as the default form field label and uses
 * `password` as the default node id.
 * 
 * @author      Matthias Schmidt
 * @copyright   2001-2020 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Form\Builder\Field\User
 * @since       5.4
 */
class UserPasswordField extends AbstractFormField implements IAutoFocusFormField, IPlaceholderFormField {
	use TAutoFocusFormField;
	use TDefaultIdFormField;
	use TPlaceholderFormField;
	
	/**
	 * @inheritDoc
	 */
	protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/Value';
	
	/**
	 * @inheritDoc
	 */
	protected $templateName = '__userPasswordFormField';
	
	/**
	 * Creates a new instance of `UserPasswordField`.
	 */
	public function __construct() {
		$this->label('wcf.user.password');
	}
	
	/**
	 * @inheritDoc
	 */
	protected static function getDefaultId() {
		return 'password';
	}
	
	/**
	 * @inheritDoc
	 */
	public function isAvailable() {
		return WCF::getUser()->userID != 0 && !WCF::getUser()->authData && parent::isAvailable();
	}
	
	/**
	 * @inheritDoc
	 */
	public function readValue() {
		if ($this->getDocument()->hasRequestData($this->getPrefixedId())) {
			$this->value = $this->getDocument()->getRequestData($this->getPrefixedId());
		}
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		if ($this->isRequired() && !$this->getValue()) {
			$this->addValidationError(new FormFieldValidationError('empty'));
		}
		else if ($this->getValue() && !WCF::getUser()->checkPassword($this->getValue())) {
			$this->addValidationError(
				new FormFieldValidationError(
					'false',
					'wcf.user.password.error.false'
				)
			);
		}
		
		parent::validate();
	}
}
