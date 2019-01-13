<?php
namespace wcf\system\upload;

/**
 * Represents a file upload.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Upload
 */
class UploadFile {
	/**
	 * original file name
	 * @var	string
	 */
	protected $filename = '';
	
	/**
	 * internal file id
	 * @var	integer
	 */
	protected $internalFileID = 0;
	
	/**
	 * location of the uploaded file
	 * @var	string
	 */
	protected $location = '';
	
	/**
	 * file size
	 * @var	integer
	 */
	protected $filesize = 0;
	
	/**
	 * file upload error code
	 * @var	integer
	 */
	protected $errorCode = 0;
	
	/**
	 * MIME type
	 * @var	string
	 */
	protected $mimeType = '';
	
	/**
	 * validation error type
	 * @var	string
	 */
	protected $validationErrorType = '';
	
	/**
	 * additional data for validation errors
	 * @var array
	 */
	protected $validationErrorAdditionalData = [];
	
	/**
	 * Creates a new UploadFile object.
	 * 
	 * @param	string		$filename
	 * @param	string		$location
	 * @param	integer		$filesize
	 * @param	integer		$errorCode
	 * @param	string		$mimeType
	 * 
	 * @throws	\Exception	if an error occurred during upload and debug mode is enabled
	 */
	public function __construct($filename, $location, $filesize, $errorCode = 0, $mimeType = '') {
		if (preg_match('~^__wcf_([0-9]+)_(.*)~', $filename, $matches)) {
			$this->internalFileID = $matches[1];
			$filename = $matches[2];
		}
		
		$this->filename = $filename;
		$this->location = $location;
		$this->filesize = $filesize;
		$this->errorCode = $errorCode;
		$this->mimeType = $mimeType;
		
		if (ENABLE_DEBUG_MODE) {
			switch ($errorCode) {
				case UPLOAD_ERR_INI_SIZE:
					throw new \Exception("The uploaded file is larger than PHP's `upload_max_filesize`.");
					
				case UPLOAD_ERR_FORM_SIZE:
					throw new \Exception("The uploaded file is larger than the form's `MAX_FILE_SIZE`.");
				
				case UPLOAD_ERR_PARTIAL:
					throw new \Exception("The uploaded file was only partially uploaded.");
				
				case UPLOAD_ERR_NO_FILE:
					throw new \Exception("No file was uploaded.");
				
				case UPLOAD_ERR_NO_TMP_DIR:
					throw new \Exception("There is no temporary folder where PHP can save the file.");
				
				case UPLOAD_ERR_CANT_WRITE:
					throw new \Exception("The uploaded file could not be written to disk.");
				
				case UPLOAD_ERR_EXTENSION:
					throw new \Exception("A PHP extension stopped the file upload.");
			}
		}
	}
	
	/**
	 * Returns the original file name.
	 * 
	 * @return	string
	 */
	public function getFilename() {
		return $this->filename;
	}
	
	/**
	 * Returns internal file id.
	 * 
	 * @return	integer
	 */
	public function getInternalFileID() {
		return $this->internalFileID;
	}
	
	/**
	 * Returns the extension of the original file name.
	 * 
	 * @return	string
	 */
	public function getFileExtension() {
		if (($position = mb_strrpos($this->getFilename(), '.')) !== false) {
			return mb_strtolower(mb_substr($this->getFilename(), $position + 1));
		}
		
		return '';
	}
	
	/**
	 * Returns the file location.
	 * 
	 * @return	string
	 */
	public function getLocation() {
		return $this->location;
	}
	
	/**
	 * Returns the file size.
	 * 
	 * @return	integer
	 */
	public function getFilesize() {
		return $this->filesize;
	}
	
	/**
	 * Returns the MIME type.
	 * 
	 * @return	string
	 */
	public function getMimeType() {
		return $this->mimeType;
	}
	
	/**
	 * Returns the error code.
	 * 
	 * @return	integer
	 */
	public function getErrorCode() {
		return $this->errorCode;
	}
	
	/**
	 * Sets the validation error type.
	 * 
	 * @param	string		$validationErrorType
	 * @param       array           $additionalData
	 */
	public function setValidationErrorType($validationErrorType, array $additionalData = []) {
		$this->validationErrorType = $validationErrorType;
		$this->validationErrorAdditionalData = $additionalData;
	}
	
	/**
	 * Returns the validation error type.
	 * 
	 * @return	string
	 */
	public function getValidationErrorType() {
		return $this->validationErrorType;
	}
	
	/**
	 * Returns the validation error additional data array.
	 * 
	 * @return	array
	 */
	public function getValidationErrorAdditionalData() {
		return $this->validationErrorAdditionalData;
	}
	
	/**
	 * Returns the image data of the file or `null` if the file is no image.
	 * 
	 * @return	array|null
	 */
	public function getImageData() {
		if (($imageData = @getimagesize($this->getLocation())) !== false) {
			return [
				'width' => $imageData[0],
				'height' => $imageData[1],
				'mimeType' => $imageData['mime']
			];
		}
		
		return null;
	}
	
	/**
	 * Moves the uploaded file to the given location and updates the internal location value to the new location
	 * and the internal filename value to the new filename derived from the given location.
	 * 
	 * @param	string		$newLocation	new file location
	 */
	public function moveUploadedFile($newLocation) {
		move_uploaded_file($this->location, $newLocation);
		
		$this->location = $newLocation;
		$this->filename = basename($this->location);
	}
}
