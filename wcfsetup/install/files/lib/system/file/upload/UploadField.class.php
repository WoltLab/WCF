<?php
namespace wcf\system\file\upload;

/**
 * An specific upload field.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\File\Upload
 * @since       5.2
 */
class UploadField {
	/**
	 * The max number of files for this field.
	 * @var int 
	 */
	public $maxFiles = 10;
	
	/**
	 * The intern field id. Should be unique for each form.
	 * @var string
	 */
	public $fieldId;
	
	/**
	 * The internalId for uploads.
	 * @var string|null 
	 */
	public $internalId = null;
	
	/**
	 * This flag indicates whether only images can uploaded via this field.
	 * @var boolean
	 */
	public $imageOnly = false;
	
	/**
	 * This flag indicates whether only images can uploaded via this field.
	 * <strong>Heads up:</strong> SVG images can contain bad code, therefore do not
	 * use this option, outside the acp or check the file whether remote code is contained.
	 * @var boolean
	 */
	public $allowSvgImage = false;
	
	/**
	 * Acceptable file types.
	 * @var string[]|null
	 * @since 5.3
	 */
	public $acceptableFiles = null;
	
	/**
	 * UploadField constructor.
	 *
	 * @param       string          $fieldId
	 */
	public function __construct($fieldId) {
		$this->fieldId = $fieldId;
	}
	
	/**
	 * Indicates the support of multiple files.
	 * 
	 * @return boolean
	 */
	public function supportMultipleFiles() {
		return $this->maxFiles === null || $this->maxFiles > 1;
	}
	
	/**
	 * Returns the max number of files.
	 * 
	 * @return int
	 */
	public function getMaxFiles() {
		return $this->maxFiles;
	}
	
	/**
	 * Returns `true` if only images can be uploaded via this field and returns `false` otherwise.
	 * 
	 * @return boolean
	 */
	public function isImageOnly() {
		return $this->imageOnly;
	}
	
	/**
	 * Returns true, if the field can contain svg images in the image only mode.
	 * 
	 * @return boolean
	 */
	public function svgImageAllowed() {
		return $this->allowSvgImage;
	}
	
	/**
	 * Returns the fieldId. 
	 * 
	 * @return string
	 */
	public function getFieldId() {
		return $this->fieldId;
	}
	
	/**
	 * Sets the internalId for this field.
	 * 
	 * @param       string          $internalId
	 */
	public function setInternalId($internalId) {
		$this->internalId = $internalId;
	}
	
	/**
	 * Returns the internalId of this field.
	 * 
	 * @return string|null
	 */
	public function getInternalId() {
		return $this->internalId;
	}
	
	/**
	 * Sets the flag for `imageOnly`. This flag indicates whether only images
	 * can uploaded via this field. Other file types will be rejected during upload.
	 * 
	 * If set to `true` will also set the acceptable types to `image/*`. If set to
	 * false it will clear the acceptable types if they are `image/*`.
	 * 
	 * @param       boolean       $imageOnly
	 */
	public function setImageOnly($imageOnly) {
		$this->imageOnly = $imageOnly;
		
		if ($imageOnly) {
			$this->setAcceptableFiles(['image/*']);
		}
		else {
			// Using == here is safe, because we match a single element array containing
			// a scalar value.
			if ($this->getAcceptableFiles() == ['image/*']) {
				$this->setAcceptableFiles(null);
			}
		}
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
	 * @param       boolean       $allowSvgImage
	 * 
	 * @throws      \BadMethodCallException         if the imageOnly flag isn't set to true
	 */
	public function setAllowSvgImage($allowSvgImage) {
		if (!$this->isImageOnly()) {
			throw new \BadMethodCallException('Allowing SVG images is only relevant, if the `imageOnly` flag is set to `true`.');
		}
		
		$this->allowSvgImage = $allowSvgImage;
	}
	
	/**
	 * Specifies acceptable file types. Use null to not specify any restrictions.
	 * 
	 * <strong>Heads up:</strong> This feature is used to improve user experience, by removing
	 * unacceptable files from the file picker. It does not validate the type of the uploaded
	 * file. You are responsible to perform (proper) validation on the server side.
	 * 
	 * Valid values are specified as "Unique file type specifiers":
	 * - A case insensitive file extension starting with a dot.
	 * - A mime type.
	 * - `audio/*`
	 * - `image/*`
	 * - `video/*`
	 * 
	 * @see         https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input/file#Unique_file_type_specifiers
	 * @param       string[]|null     $acceptableFiles
	 * @since       5.3
	 */
	public function setAcceptableFiles($acceptableFiles = null) {
		$this->acceptableFiles = $acceptableFiles;
	}
	
	/**
	 * Returns the acceptable file types.
	 * 
	 * @return      string[]|null
	 * @since       5.3
	 */
	public function getAcceptableFiles() {
		return $this->acceptableFiles;
	}
}
