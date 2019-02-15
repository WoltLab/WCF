<?php
namespace wcf\system\form\builder\field;
use wcf\data\IStorableObject;
use wcf\system\file\upload\UploadField;
use wcf\system\file\upload\UploadFile;
use wcf\system\file\upload\UploadHandler;
use wcf\system\form\builder\field\data\processor\CustomFormFieldDataProcessor;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\IFormDocument;

/**
 * Implementation of a form field for to uploads.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2019 WoltLab GmbH
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
	 * This flag indicates whether only images can uploaded via this field.
	 * <strong>Heads up:</strong> SVG images can contain bad code, therefore do not
	 * use this option, outside the acp or check the file whether remote code is contained.
	 * @var boolean
	 */
	protected $imageOnly = false;
	
	/**
	 * This flag indicates whether SVG images are treated as image in the image only mode.
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
		$uploadField->setAllowSvgImage($this->svgImageAllowed());
		
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
	 * Returns the removed files for the field. 
	 * 
	 * @param       bool    $processFiles
	 * @return      UploadFile[]
	 */
	public function getRemovedFiles($processFiles = false) {
		$this->registerField();
		
		return UploadHandler::getInstance()->getRemovedFiledByFieldId($this->getId(), $processFiles);
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
	 * @throws \InvalidArgumentException    if the getter for the value provides invalid values
	 */
	public function loadValueFromObject(IStorableObject $object) {
		// first check, whether an getter for the field exists
		if (method_exists($object, 'get'. ucfirst($this->getObjectProperty()) . 'UploadFileLocations')) {
			$value = call_user_func([$object, 'get'. ucfirst($this->getObjectProperty()) . 'UploadFileLocations']);
			$method = "method '" . get_class($object) . "::get" . ucfirst($this->getObjectProperty()) . "UploadFileLocations()'";
		}
		else if (method_exists($object, 'get'. ucfirst($this->getObjectProperty()))) {
			$value = call_user_func([$object, 'get'. ucfirst($this->getObjectProperty())]);
			$method = "method '" . get_class($object) . "::get" . ucfirst($this->getObjectProperty()) . "()'";
		}
		else {
			$value = $object->{$this->getObjectProperty()};
			$method = "variable '" . get_class($object) . "::$" . $this->getObjectProperty() . "'";
		}
		
		if (is_array($value)) {
			$value = array_map(function ($v) use ($method) {
				if (!is_string($v) || !file_exists($v)) {
					throw new \InvalidArgumentException("The " . $method . " must return an array of strings with the file locations.");
				}
				return new UploadFile($v, basename($v), UploadHandler::isValidImage($v, basename($v), $this->svgImageAllowed()), true, $this->svgImageAllowed());
			}, $value);
			
			$this->value($value);
		}
		else {
			throw new \InvalidArgumentException("The " . $method . " must return an array of strings with the file locations.");
		}
		
		return $this;
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
	 */
	public function populate() {
		parent::populate();
		
		$this->getDocument()->getDataHandler()->add(new CustomFormFieldDataProcessor('upload', function(IFormDocument $document, array $parameters) {
			$parameters[$this->getId()] = $this->getValue();
			$parameters[$this->getId() . '_removedFiles'] = $this->getRemovedFiles(true);
			
			return $parameters;
		}));
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 * 
	 * @throws      \RuntimeException       if the field has already been initialized
	 */
	public function maximum($maximum = null) {
		if ($this->isRegistered()) {
			throw new \LogicException('The upload field has already been registered. Therefore no modifications are allowed.');
		}
		
		return $this->traitMaximum($maximum);
	}
	
	/**
	 * Sets the flag for `imageOnly`. This flag indicates whether only images 
	 * can uploaded via this field. Other file types will be rejected during upload.
	 *
	 * @param	boolean	        $imageOnly
	 * @return	static				this field
	 */
	public function imageOnly($imageOnly = true) {
		$this->imageOnly = $imageOnly;
		
		return $this;
	}
	
	/**
	 * Sets the flag for `allowSvgImage`. This flag indicates whether 
	 * SVG images should be handled as image, if the upload field is 
	 * image only (if this field is not image only, this method will 
	 * throw an exception). 
	 * 
	 * <strong>Heads up:</strong> SVG images can contain bad code, therefore do not
	 * use this option, outside the acp or check the file whether remote code is contained.
	 *
	 * @param	boolean	        $allowSvgImages
	 * @return	static				this field
	 * 
	 * @throws      \BadMethodCallException         if the imageOnly flag isn't set to true
	 */
	public function allowSvgImage($allowSvgImages = true) {
		if (!$this->isImageOnly()) {
			throw new \BadMethodCallException('Allowing SVG images is only relevant, if the `imageOnly` flag is set to `true`.');
		}
		
		$this->allowSvgImage = $allowSvgImages;
		
		return $this;
	}
	
	/**
	 * Returns `true` if only images can be uploaded via this field and returns `false` otherwise.
	 *
	 * @return	boolean
	 */
	public function isImageOnly() {
		return $this->imageOnly;
	}
	
	/**
	 * Returns true, if the field can contain svg images in the image only mode.
	 *
	 * @return	boolean
	 */
	public function svgImageAllowed() {
		return $this->allowSvgImage;
	}
}
