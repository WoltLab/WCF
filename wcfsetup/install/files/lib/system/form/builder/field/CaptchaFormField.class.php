<?php
namespace wcf\system\form\builder\field;
use wcf\system\captcha\ICaptchaHandler;
use wcf\system\exception\SystemException;

/**
 * Implementation of a form field for a captcha.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	5.2
 */
class CaptchaFormField extends AbstractFormField implements IObjectTypeFormField {
	use TDefaultIdFormField;
	use TObjectTypeFormField {
		objectType as defaultObjectType;
	}
	
	/**
	 * @inheritDoc
	 */
	public function cleanup() {
		try {
			/** @var ICaptchaHandler $captcha */
			$captcha = $this->getObjectType()->getProcessor();
			
			$captcha->reset();
		}
		catch (\BadMethodCallException $e) {
			// ignore
		}
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getHtml() {
		/** @var ICaptchaHandler $captcha */
		$captcha = $this->getObjectType()->getProcessor();
		
		return $captcha->getFormElement();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getObjectTypeDefinition() {
		return 'com.woltlab.wcf.captcha';
	}
	
	/**
	 * @inheritDoc
	 */
	public function hasSaveValue() {
		return false;
	}
	
	/**
	 * @inheritDoc
	 */
	public function objectType($objectType) {
		// ignore empty object type which is the case if no captcha has been set
		if ($objectType === '') {
			return $this;
		}
		
		return $this->defaultObjectType($objectType);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readValue() {
		/** @var ICaptchaHandler $captcha */
		$captcha = $this->getObjectType()->getProcessor();
		
		// the captcha API relies on `$_POST` thus make sure that request data is in `$_POST`,
		// at least temporarily
		$requestData = $this->getDocument()->getRequestData();
		$post = null;
		if ($requestData !== $_POST) {
			$post = $_POST;
			$_POST = $requestData;
		}
		
		$captcha->readFormParameters();
		
		// restore `$_POST`
		if ($post !== null) {
			$_POST = $post;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		/** @var ICaptchaHandler $captcha */
		$captcha = $this->getObjectType()->getProcessor();
		
		$captcha->validate();
	}
	
	/**
	 * @inheritDoc
	 */
	protected static function getDefaultId() {
		return 'captcha';
	}
}
