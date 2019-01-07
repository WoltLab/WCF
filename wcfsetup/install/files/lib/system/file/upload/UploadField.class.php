<?php
namespace wcf\system\file\upload;
use wcf\system\WCF;

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
	 * 
	 * @var int 
	 */
	public $maxFiles = 10;
	
	/**
	 * The intern field id. Should be unique for each form.
	 * 
	 * @var string
	 */
	public $fieldId;
	
	/**
	 * The internalId for uploads.
	 * 
	 * @var string|null 
	 */
	public $internalId = null;
	
	/**
	 * The name of the field.
	 * 
	 * @var string 
	 */
	public $name;
	/**
	 * The description of the field.
	 * 
	 * @var tring 
	 */
	public $description;
	
	/**
	 * Indicates whether the field is image only.
	 * 
	 * @var boolean
	 */
	public $imageOnly = false;
	
	/**
	 * UploadField constructor.
	 *
	 * @param       String          $fieldId
	 * @param       String          $fieldName
	 * @param       String          $fieldDescription
	 */
	public function __construct($fieldId, $fieldName = 'Upload', $fieldDescription = null) {
		$this->fieldId = $fieldId;
		$this->name = $fieldName;
		$this->description = $fieldDescription;
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
	 * Returns the fieldId. 
	 * 
	 * @return String
	 */
	public function getFieldId() {
		return $this->fieldId;
	}
	
	/**
	 * Sets the internalId for this field.
	 * 
	 * @param       String          $internalId
	 */
	public function setInternalId($internalId) {
		$this->internalId = $internalId;
	}
	
	/**
	 * Returns the internalId of this field.
	 * 
	 * @return String|null
	 */
	public function getInternalId() {
		return $this->internalId;
	}
	
	/**
	 * Returns the name of the field. 
	 * 
	 * @return String
	 */
	public function getName() {
		return WCF::getLanguage()->get($this->name);
	}
	
	/**
	 * Returns the description of the field.
	 *
	 * @return String
	 */
	public function getDescription() {
		return WCF::getLanguage()->get($this->description);
	}
	
	/**
	 * Set the image only flag. 
	 * 
	 * @param       boolean       $imageOnly
	 */
	public function setImageOnly($imageOnly) {
		$this->imageOnly = (bool) $imageOnly;
	}
}
