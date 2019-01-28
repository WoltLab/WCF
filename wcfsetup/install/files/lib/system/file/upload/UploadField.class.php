<?php
namespace wcf\system\file\upload;

/**
 * An specific upload field.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2018 WoltLab GmbH
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
	 * Indicates whether the field is image only.
	 * @var boolean
	 */
	public $imageOnly = false;
	
	/**
	 * Indicates whether the field supports svg images.
	 * 
	 * <strong>Heads up:</strong> svg images can contain code, therefore do not
	 * use this option, outside the acp or check the file whether remote code is contained.
	 * 
	 * @var boolean
	 */
	public $allowSvgImages = false;
	
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
		return $this->maxFiles > 1;
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
	 * Returns true, if the upload is image only.
	 * 
	 * @return boolean
	 */
	public function isImageOnly() {
		return $this->imageOnly;
	}
	
	/**
	 * Returns true, if svg images are allowed.
	 * 
	 * @return boolean
	 */
	public function svgImagesAllowed() {
		return $this->allowSvgImages;
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
	 * Set the image only flag. 
	 * 
	 * @param       boolean       $imageOnly
	 */
	public function setImageOnly($imageOnly) {
		$this->imageOnly = $imageOnly;
	}
	
	/**
	 * Set the image only flag. 
	 * 
	 * @param       boolean       $allowSvgImages
	 */
	public function setAllowSvgImages($allowSvgImages) {
		$this->allowSvgImages = $allowSvgImages;
	}
}
