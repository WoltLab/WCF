<?php
namespace wcf\system\form\builder\field;
use wcf\system\file\upload\UploadField;
use wcf\system\file\upload\UploadFile;
use wcf\system\file\upload\UploadHandler;
use wcf\system\form\builder\field\validation\FormFieldValidationError;

/**
 * Implementation of a form field for to uploads.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	5.2
 */
class UploadFormField extends AbstractFormField {
	use TMaximumFormField {
		maximum as traitMaximum;
	}
	use TMinimumFormField;
	
	/**
	 * imageOnly flag for the upload field.
	 * @var boolean
	 */
	protected $imageOnly = false;
	
	/**
	 * allowSvgImage flag.
	 * @var boolean
	 */
	protected $allowSvgImage = false;
	
	/**
	 * @inheritDoc
	 */
	protected $templateName = '__uploadFormField';
	
	/**
	 * Registers the current field in the upload handler.
	 */
	private function registerField() {
		if (!UploadHandler::getInstance()->isRegisteredFieldId($this->getId())) {
			UploadHandler::getInstance()->registerUploadField($this->buildUploadField());
		}
	}
	
	/**
	 * Unregisters the current field in the upload handler.
	 */
	private function unregisterField() {
		if (UploadHandler::getInstance()->isRegisteredFieldId($this->getId())) {
			UploadHandler::getInstance()->unregisterUploadField($this->getId());
		}
	}
	
	/**
	 * Builds the UploadField class. 
	 * 
	 * @return      UploadField
	 */
	protected function buildUploadField() {
		$uploadField = new UploadField($this->getId());
		$uploadField->maxFiles = $this->getMaximum();
		$uploadField->setImageOnly($this->isImageOnly());
		$uploadField->setAllowSvgImages($this->svgImageAllowed());
		
		return $uploadField;
	}
	
	/**
	 * Returns true, iff the current field is already registered. 
	 * 
	 * @return boolean
	 */
	private function isRegistered() {
		return UploadHandler::getInstance()->isRegisteredFieldId($this->getId());
	}
	
	/**
	 * @inheritDoc
	 * @return      UploadFile[]
	 */
	public function getValue() {
		$this->registerField();
		
		return UploadHandler::getInstance()->getFilesByFieldId($this->getId());
	}
	
	/**
	 * @inheritDoc
	 */
	public function getObjectProperty() {
		return null;
	}
	
	/**
	 * @inheritDoc
	 */
	public function readValue() {
		$this->registerField(); 
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		parent::validate();
		
		if (empty($this->getValue())) {
			if ($this->isRequired()) {
				$this->addValidationError(new FormFieldValidationError('empty'));
			}
		}
		
		if ($this->getMinimum() !== null && count($this->getValue()) < $this->getMinimum()) {
			$this->addValidationError(new FormFieldValidationError(
				'minimum',
				'wcf.form.field.upload.error.minimum',
				['minimum' => $this->getMinimum()]
			));
		}
		else if ($this->getMaximum() !== null && count($this->getValue()) > $this->getMaximum()) {
			$this->addValidationError(new FormFieldValidationError(
				'maximum',
				'wcf.form.field.upload.error.maximum',
				['maximum' => $this->getMaximum()]
			));
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function getHtml() {
		$this->registerField();
		
		return parent::getHtml();
	}
	
	/**
	 * @inheritDoc
	 * 
	 * @param       UploadFile[]    $value
	 * 
	 * @throws      \InvalidArgumentException       if the value is not an array
	 * @throws      \InvalidArgumentException       if the value contains objects, which are not an instance of UploadFile
	 */
	public function value($value) {
		if (!is_array($value)) {
			throw new \InvalidArgumentException('$value must be an array.');
		}
		
		foreach ($value as $file) {
			if (!($file instanceof UploadFile)) {
				throw new \InvalidArgumentException('All given files must be an instance of '. UploadFile::class .'.');
			}
		}
		
		$this->registerField();
		
		UploadHandler::getInstance()->registerFilesByField($this->getId(), $value);
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
	public function cleanup() {
		$this->unregisterField();
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 * 
	 * @throws      \RuntimeException       if the field has already been initalisated
	 */
	public function maximum($maximum = null) {
		if ($this->isRegistered()) {
			throw new \LogicException('The upload field has already been registered. Therefore no modifications are allowed.');
		}
		
		return $this->traitMaximum($maximum);
	}
	
	/**
	 * Sets the imageOnly flag for this field.
	 *
	 * @param	boolean	        $imageOnly
	 * @return	static				this field
	 */
	public function imageOnly($imageOnly = true) {
		$this->imageOnly = $imageOnly;
		
		return $this;
	}
	
	/**
	 * Sets the imageOnly flag for this field.
	 *
	 * @param	boolean	        $allowSvgImages
	 * @return	static				this field
	 */
	public function allowSvgImage($allowSvgImages = true) {
		$this->allowSvgImage = $allowSvgImages;
		
		return $this;
	}
	
	/**
	 * Returns true, if the field is an image only field (only images can be uploaded).
	 *
	 * @return	boolean
	 */
	public function isImageOnly() {
		return $this->imageOnly;
	}
	
	/**
	 * Returns true, if the field can contain svg images.
	 *
	 * @return	boolean
	 */
	public function svgImageAllowed() {
		return $this->allowSvgImage;
	}
}
