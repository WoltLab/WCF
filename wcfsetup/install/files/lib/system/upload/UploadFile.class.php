<?php
namespace wcf\system\upload;

/**
 * Represents a file upload.
 *
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.upload
 * @category 	Community Framework
 */
class UploadFile {
	/**
	 * original file name
	 * @var string
	 */
	protected $filename = '';
	
	/**
	 * location of the uploaded file
	 * @var string
	 */
	protected $location = '';
	
	/**
	 * file size
	 * @var integer
	 */
	protected $filesize = 0;
	
	/**
	 * file upload error code
	 * @var integer
	 */
	protected $errorCode = 0;
	
	/**
	 * MIME type
	 * @var string
	 */
	protected $mimeType = '';
	
	/**
	 * validation error type
	 * @var string
	 */
	protected $validationErrorType = '';
	
	/**
	 * Creates a new UploadFile object.
	 * 
	 * @param	string		$filename
	 * @param	string		$location
	 * @param 	integer		$filesize
	 * @param	integer		$errorCode
	 * @param	string		$mimeType
	 */
	public function __construct($filename, $location, $filesize, $errorCode = 0, $mimeType = '') {
		$this->filename = $filename;
		$this->location = $location;
		$this->filesize = $filesize;
		$this->errorCode = $errorCode;
		$this->mimeType = $mimeType;
	}
	
	/**
	 * Returns the original file name.
	 * 
	 * @return string
	 */
	public function getFilename() {
		return $this->name;
	}
	
	/**
	 * Returns the file location.
	 * 
	 * @return string
	 */
	public function getLocation() {
		return $this->location;
	}
	
	/**
	 * Returns the file size.
	 * 
	 * @return integer
	 */
	public function getFilesize() {
		return $this->filesize;
	}
	
	/**
	 * Returns the MIME type.
	 * 
	 * @return string
	 */
	public function getMimeType() {
		return $this->mimeType;
	}
	
	/**
	 * Returns the error code.
	 * 
	 * @return integer
	 */
	public function getErrorCode() {
		return $this->errorCode;
	}
	
	/**
	 * Validates the uploaded file. Returns true on success, otherwise false.
	 * 
	 * @param	integer		$maxFilesize
	 * @param	array<string>	$fileExtensions
	 * @return	boolean
	 */
	public function validateFile($maxFilesize, array $fileExtensions) {
		
	}
	
	/**
	 * Returns the validation error type.
	 * 
	 * @return string
	 */
	public function getValidationErrorType() {
		return $this->validationErrorType;
	}
}
