<?php
namespace wcf\system\upload;

/**
 * Handles file uploads.
 *
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.upload
 * @category 	Community Framework
 */
class UploadHandler {
	/**
	 * list of uploaded files
	 * @var array<wcf\system\upload\UploadFile>
	 */
	protected $files = array();
	
	/**
	 * list of validation errors.
	 * @var array
	 */
	protected $errors = array();
	
	/**
	 * Creates a new UploadHandler object.
	 * 
	 * @param	array<mixed>	$rawFileData
	 */
	protected function __construct(array $rawFileData) {
		if (is_array($rawFileData['name'])) {
			// multiple uploads
			for ($i = 0, $l = count($rawFileData['name']); $i < $l; $i++) {
				$this->files[] = new UploadFile($rawFileData['name'][$i], $rawFileData['tmp_name'][$i], $rawFileData['size'][$i], $rawFileData['error'][$i], $rawFileData['type'][$i]);
			}
		}
		else {
			$this->files[] = new UploadFile($rawFileData['name'], $rawFileData['tmp_name'], $rawFileData['size'], $rawFileData['error'], $rawFileData['type']);
		}
	}
	
	/**
	 * Returns the list of uploaded files.
	 * 
	 * @return array<wcf\system\upload\UploadFile>
	 */
	public function getFiles() {
		return $this->files;
	}
	
	/**
	 * Validates the uploaded files. Returns true on success, otherwise false.
	 * 
	 * @param	integer		$maxFilesize
	 * @param	array<string>	$fileExtensions
	 * @return	boolean
	 */
	public function validateFiles($maxFilesize, array $fileExtensions) {
		$result = true;
		foreach ($this->files as $file) {
			if (!$file->validateFile($maxFilesize, $fileExtensions)) {
				$this->errors[$file->getFilename()] = $file->getValidationErrorType();
				$result = false;
			}
		}
		
		return $result;
	}
	
	/**
	 * Returns a list of validation errors.
	 * 
	 * @return array
	 */
	public function getErrors() {
		return $this->errors;
	}
	
	/**
	 * Saves the uploaded files.
	 * 
	 * @param	wcf\system\upload\IUploadFileSaveStrategy	$saveStrategy
	 */
	public function saveFiles(IUploadFileSaveStrategy $saveStrategy) {
		foreach ($this->files as $file) {
			$saveStrategy->save($file);
		}
	}
	
	/**
	 * Gets an upload handler instance.
	 * 
	 * @param	string		$identifier
	 * @return	wcf\system\upload\UploadHandler
	 */
	public static function getUploadHandler($identifier) {
		if (isset($_FILES[$identifier]) && is_array($_FILES[$identifier])) return new UploadHandler($_FILES[$identifier]);
		
		return null;
	}
}
