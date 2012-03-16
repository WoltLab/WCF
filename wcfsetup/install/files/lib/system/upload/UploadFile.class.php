<?php
namespace wcf\system\upload;
use wcf\util\StringUtil;

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
		return $this->filename;
	}
	
	/**
	 * Returns the extension of the original file name.
	 * 
	 * @return string
	 */
	public function getFileExtension() {
		if (($position = StringUtil::lastIndexOf($this->getFilename(), '.')) !== false) {
			return StringUtil::toLowerCase(StringUtil::substring($this->getFilename(), $position + 1));
		}
		
		return '';
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
		if ($this->errorCode != 0) {
			$this->validationErrorType = 'uploadFailed';
			return false;
		}
		
		if ($this->getFilesize() > $maxFilesize) {
			$this->validationErrorType = 'tooLarge';
			return false;
		}
		
		if (!in_array($this->getFileExtension(), $fileExtensions)) {
			$this->validationErrorType = 'invalidExtension';
			return false;
		}
		
		return true;
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
			if (($imageData = @getImageSize($this->getLocation())) !== false) {
				return array(
					'width' => $imageData[0],
					'height' => $imageData[1],
					'mimeType' => $imageData['mime'],
				);
			}
		}
		
		return null;
	}
}
