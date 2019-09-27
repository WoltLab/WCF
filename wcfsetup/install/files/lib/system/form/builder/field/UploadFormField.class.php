<?php
namespace wcf\system\form\builder\field;
use wcf\data\IStorableObject;
use wcf\system\file\upload\UploadField;
use wcf\system\file\upload\UploadFile;
use wcf\system\file\upload\UploadHandler;
use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
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
	 * @inheritDoc
	 */
	protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/Value';
	
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
	 * maximum filesize for each uploaded file
	 * @var	null|number
	 */
	protected $maximumFilesize;
	
	/**
	 * minimum image width for each uploaded file
	 * @var	null|number
	 */
	protected $minimumImageWidth;
	
	/**
	 * maximum image width for each uploaded file
	 * @var	null|number
	 */
	protected $maximumImageWidth;
	
	/**
	 * @inheritDoc
	 */
	protected $templateName = '__uploadFormField';
	
	/**
	 * Array of temporary values, which are assigned, after the method `populate` are called.
	 * @var array 
	 */
	private $values = [];
	
	/**
	 * Unregisters the current field in the upload handler.
	 */
	private function unregisterField() {
		if (UploadHandler::getInstance()->isRegisteredFieldId($this->getPrefixedId())) {
			UploadHandler::getInstance()->unregisterUploadField($this->getPrefixedId());
		}
	}
	
	/**
	 * Builds the UploadField class. 
	 * 
	 * @return      UploadField
	 */
	protected function buildUploadField() {
		$uploadField = new UploadField($this->getPrefixedId());
		$uploadField->maxFiles = $this->getMaximum();
		$uploadField->setImageOnly($this->isImageOnly());
		if ($this->isImageOnly()) {
			$uploadField->setAllowSvgImage($this->svgImageAllowed());
		}
		
		return $uploadField;
	}
	
	/**
	 * Returns true, iff the current field is already registered. 
	 * 
	 * @return boolean
	 */
	private function isRegistered() {
		return $this->isPopulated;
	}
	
	/**
	 * @inheritDoc
	 * @return      UploadFile[]
	 * @throws      \BadMethodCallException         if the method is called, before the field is populated
	 */
	public function getValue() {
		if (!$this->isPopulated) {
			throw new \BadMethodCallException("The field must be populated, before calling this method.");
		}
		
		return UploadHandler::getInstance()->getFilesByFieldId($this->getPrefixedId());
	}
	
	/**
	 * Returns the removed files for the field. 
	 * 
	 * @param       bool    $processFiles
	 * @return      UploadFile[]
	 * @throws      \BadMethodCallException         if the method is called, before the field is populated
	 */
	public function getRemovedFiles($processFiles = false) {
		if (!$this->isPopulated) {
			throw new \BadMethodCallException("The field must be populated, before calling the method.");
		}
		
		return UploadHandler::getInstance()->getRemovedFiledByFieldId($this->getPrefixedId(), $processFiles);
	}
	
	/**
	 * @inheritDoc
	 * @throws      \BadMethodCallException         if the method is called, before the field is populated
	 */
	public function readValue() {
		if (!$this->isPopulated) {
			throw new \BadMethodCallException("The field must be populated, before calling this method.");
		}
		
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
		
		if ($this->getMaximumFilesize() !== null) {
			foreach ($this->getValue() as $file) {
				$filesize = filesize($file->getLocation());
				if ($filesize > $this->getMaximumFilesize()) {
					$this->addValidationError(new FormFieldValidationError(
						'maximumFilesize',
						'wcf.form.field.upload.error.maximumFilesize',
						[
							'maximumFilesize' => $this->getMaximumFilesize(),
							'file' => $file
						]
					));
				}
			}
		}
		
		if ($this->getMinimumImageWidth() !== null || $this->getMaximumImageWidth() !== null) {
			foreach ($this->getValue() as $file) {
				$imagesize = getimagesize($file->getLocation());
				
				if ($this->getMinimumImageWidth() !== null && $this->getMinimumImageWidth() >= $imagesize[0]) {
					$this->addValidationError(new FormFieldValidationError(
						'minimumImageWidth',
						'wcf.form.field.upload.error.minimumImageWidth',
						[
							'minimumImageWidth' => $this->getMinimumImageWidth(),
							'file' => $file
						]
					));
				}
				else if ($this->getMaximumImageWidth() !== null && $imagesize[0] > $this->getMaximumImageWidth()) {
					$this->addValidationError(new FormFieldValidationError(
						'maximumImageWidth',
						'wcf.form.field.upload.error.maximumImageWidth',
						[
							'maximumImageWidth' => $this->getMaximumImageWidth(),
							'file' => $file
						]
					));
				}
			}
		}
	}
	
	/**
	 * @inheritDoc
	 * @throws      \BadMethodCallException         if the method is called, before the field is populated
	 */
	public function getHtml() {
		if (!$this->isPopulated) {
			throw new \BadMethodCallException("The field must be populated, before calling this method.");
		}
		
		return parent::getHtml();
	}
	
	/**
	 * @inheritDoc
	 * 
	 * @throws \InvalidArgumentException    if the getter for the value provides invalid values
	 */
	public function loadValue(array $data, IStorableObject $object) {
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
			$value = $data[$this->getObjectProperty()];
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
		
		if ($this->isPopulated) {
			UploadHandler::getInstance()->registerFilesByField($this->getPrefixedId(), $value);
		}
		else {
			$this->values = $value;
		}
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
		
		UploadHandler::getInstance()->registerUploadField($this->buildUploadField(), $this->getDocument()->getRequestData());
		
		if (!empty($this->values)) {
			UploadHandler::getInstance()->registerFilesByField($this->getPrefixedId(), $this->values);
		}
		
		$this->getDocument()->getDataHandler()->addProcessor(new CustomFormDataProcessor('upload', function(IFormDocument $document, array $parameters) {
			$parameters[$this->getObjectProperty()] = $this->getValue();
			$parameters[$this->getObjectProperty() . '_removedFiles'] = $this->getRemovedFiles(true);
			
			return $parameters;
		}));
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 * 
	 * @throws      \LogicException       if the field has already been initialized
	 */
	public function maximum($maximum = null) {
		if ($this->isRegistered()) {
			throw new \LogicException('The upload field has already been registered. Therefore no modifications are allowed.');
		}
		
		return $this->traitMaximum($maximum);
	}
	
	/**
	 * Sets the maximum filesize for each upload. If `null` is passed, the
	 * maximum filesize is removed.
	 *
	 * @param	null|number	$maximumFilesize	        maximum filesize
	 * @return	static				        this field
	 *
	 * @throws	\InvalidArgumentException	if the given maximum filesize is no number or otherwise invalid
	 */
	public function maximumFilesize($maximumFilesize = null) {
		if ($maximumFilesize !== null) {
			if (!is_numeric($maximumFilesize)) {
				throw new \InvalidArgumentException("Given maximum filesize is no int, '" . gettype($maximumFilesize) . "' given.");
			}
		}
		
		$this->maximumFilesize = $maximumFilesize;
		
		return $this;
	}
	
	/**
	 * Returns the maximum filesize of each file or `null` if no maximum filesize
	 * has been set.
	 *
	 * @return	null|number
	 */
	public function getMaximumFilesize() {
		return $this->maximumFilesize;
	}
	
	/**
	 * Sets the minimum image width for each uploaded file. If `null` is passed, the
	 * minimum image width is removed.
	 *
	 * @param	null|number	$minimumImageWidth      the mimimum image width
	 * @return	static				       this field
	 *
	 * @throws	\InvalidArgumentException	if the given mimimum image width is no number or otherwise invalid
	 * @throws	\LogicException	                 if the form field is not marked as image only
	 */
	public function minimumImageWidth($minimumImageWidth = null) {
		if (!$this->isImageOnly()) {
			throw new \LogicException("The form field must be image only, to set a minimum image width.");
		}
		
		if ($minimumImageWidth !== null) {
			if (!is_numeric($minimumImageWidth)) {
				throw new \InvalidArgumentException("Given minimum image width is no int, '" . gettype($minimumImageWidth) . "' given.");
			}
			
			$maximumImageWidth = $this->getMaximumImageWidth();
			if ($maximumImageWidth !== null && $minimumImageWidth > $maximumImageWidth) {
				throw new \InvalidArgumentException("Minimum image width ({$minimumImageWidth}) cannot be greater than maximum image width ({$maximumImageWidth}).");
			}
		}
		
		$this->minimumImageWidth = $minimumImageWidth;
		
		return $this;
	}
	
	/**
	 * Returns the mimimum image width of each file or `null` if no mimimum image width
	 * has been set.
	 *
	 * @return	null|number
	 */
	public function getMinimumImageWidth() {
		return $this->minimumImageWidth;
	}
	
	/**
	 * Sets the maximum image width for each uploaded file. If `null` is passed, the
	 * maximum image width is removed.
	 *
	 * @param	null|number	$maximumImageWidth      the maximum image width
	 * @return	static				       this field
	 *
	 * @throws	\InvalidArgumentException	if the given mimimum image width is no number or otherwise invalid
	 * @throws	\LogicException	                 if the form field is not marked as image only
	 */
	public function maximumImageWidth($maximumImageWidth = null) {
		if (!$this->isImageOnly()) {
			throw new \LogicException("The form field must be image only, to set a maximum image width.");
		}
		
		if ($maximumImageWidth !== null) {
			if (!is_numeric($maximumImageWidth)) {
				throw new \InvalidArgumentException("Given maximum image width is no int, '" . gettype($maximumImageWidth) . "' given.");
			}
			
			$minimumImageWidth = $this->getMinimumImageWidth();
			if ($maximumImageWidth !== null && $minimumImageWidth > $maximumImageWidth) {
				throw new \InvalidArgumentException("Maximum image width ({$maximumImageWidth}) cannot be smaller than minimum image width ({$minimumImageWidth}).");
			}
		}
		
		$this->maximumImageWidth = $maximumImageWidth;
		
		return $this;
	}
	
	/**
	 * Returns the maximum image width of each file or `null` if no maximum image width
	 * has been set.
	 *
	 * @return	null|number
	 */
	public function getMaximumImageWidth() {
		return $this->maximumImageWidth;
	}
	
	/**
	 * Sets the flag for `imageOnly`. This flag indicates whether only images 
	 * can uploaded via this field. Other file types will be rejected during upload.
	 *
	 * @param	boolean	        $imageOnly
	 * @return	static				this field
	 * 
	 * @throws       \InvalidArgumentException         if the field is not set to images only and a minimum/maximum width is set
	 */
	public function imageOnly($imageOnly = true) {
		if ($imageOnly && $this->getMinimumImageWidth() !== null) {
			throw new \InvalidArgumentException("The form field must be image only, because a minimum image width is set.");
		}
		
		if ($imageOnly && $this->getMaximumImageWidth() !== null) {
			throw new \InvalidArgumentException("The form field must be image only, because a maximum image width is set.");
		}
		
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
