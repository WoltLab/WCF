<?php
namespace wcf\system\upload;

/**
 * Represents a file upload.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
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
	 * Creates a new UploadFile object.
	 * 
	 * @param	string		$filename
	 * @param	string		$location
	 * @param	integer		$filesize
	 * @param	integer		$errorCode
	 * @param	string		$mimeType
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
	 */
	public function setValidationErrorType($validationErrorType) {
		$this->validationErrorType = $validationErrorType;
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
	 * Gets image data.
	 * 
	 * @return	array
	 */
	public function getImageData() {
		if (strpos($this->getMimeType(), 'image/') == 0) {
			if (($imageData = @getimagesize($this->getLocation())) !== false) {
				return [
					'width' => $imageData[0],
					'height' => $imageData[1],
					'mimeType' => $imageData['mime']
				];
			}
		}
		
		return null;
	}
}
