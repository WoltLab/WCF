<?php
namespace wcf\system\form\builder\container;
use wcf\system\event\EventHandler;
use wcf\system\form\builder\field\CaptchaFormField;
use wcf\system\form\builder\field\EmailFormField;
use wcf\system\form\builder\field\user\UsernameFormField;

/**
 * Represents the whole container with a username, email and captcha input for forms supporting content generated by
 * guests.
 * 
 * Instead of having to manually set up each individual component, this form container allows to
 * simply create an instance of this class, set some required data for some components, and the
 * setup is complete.
 * 
 * @author	Florian Gail
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Container
 * @since	5.2
 */
class GuestFormContainer extends FormContainer {
	/**
	 * `true` if the username field has to be filled out and `false` otherwise
	 * @var	boolean
	 */
	protected $required = false;
	
	/**
	 * is `true` if the guest form field will support email address, otherwise `false`
	 * @var	boolean
	 */
	protected $supportEmail = false;
	
	/**
	 * `true` if the email field has to be filled out and `false` otherwise
	 * @var	boolean
	 */
	protected $emailRequired = false;
	
	/**
	 * is `true` if the guest form field will support a captcha field, otherwise `false`
	 * @var	boolean
	 */
	protected $supportCaptcha = false;
	
	/**
	 * @inheritDoc
	 * @return	static
	 */
	public static function create($id) {
		if (empty($id)) $id = 'guest';
		return parent::create($id . 'Container');
	}
	
	/**
	 * Returns `true` if the username field has to be filled out and returns `false` otherwise.
	 * By default, the username field does not have to be filled out.
	 * 
	 * @return	boolean
	 */
	public function isRequired() {
		return $this->required;
	}
	
	/**
	 * @inheritDoc
	 */
	public function populate() {
		parent::populate();
		
		$this->appendChild(
			UsernameFormField::create('username')
				->required($this->isRequired())
		);
		
		if ($this->supportEmail) {
			$this->appendChild(
				EmailFormField::create('email')
					->required($this->emailRequired)
			);
		}
		
		if ($this->supportCaptcha) {
			$this->appendChild(
				CaptchaFormField::create()
					->required()
					->objectType(CAPTCHA_TYPE)
			);
		}
		
		EventHandler::getInstance()->fireAction($this, 'populate');
	}
	
	/**
	 * Sets whether it is required to fill out the username field and returns this container.
	 *
	 * @param	boolean		$required	determines if field has to be filled out
	 * @return	static				this container
	 */
	public function required($required = true) {
		$this->required = $required;
		
		return $this;
	}
	
	/**
	 * Sets if an email input is supported and returns this form container.
	 * 
	 * By default, email inputs are not supported.
	 * 
	 * @param	boolean		$supportEmail
	 * @return	static		this form container
	 */
	public function supportEmail($supportEmail = true) {
		$this->supportEmail = $supportEmail;
		
		return $this;
	}
	
	/**
	 * Sets whether it is required to fill out the email field and returns this container.
	 *
	 * @param	boolean    $emailRequired	determines if field has to be filled out
	 * @return	static				this container
	 */
	public function emailRequired($emailRequired = true) {
		$this->emailRequired = $emailRequired;
		
		return $this;
	}
	
	/**
	 * Sets if an captcha should be shown and returns this form container.
	 *
	 * By default, captcha validations is not required.
	 *
	 * @param	boolean		$supportCaptcha
	 * @return	static		this form container
	 */
	public function supportCaptcha($supportCaptcha = true) {
		$this->supportCaptcha = $supportCaptcha;
		
		return $this;
	}
}
