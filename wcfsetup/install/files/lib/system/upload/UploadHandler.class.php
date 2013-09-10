<?php
namespace wcf\system\upload;
use wcf\util\FileUtil;

/**
 * Handles file uploads.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.upload
 * @category	Community Framework
 */
class UploadHandler {
	/**
	 * list of uploaded files
	 * @var	array<wcf\system\upload\UploadFile>
	 */
	protected $files = array();
	
	/**
	 * list of validation errors.
	 * @var	array
	 */
	protected $erroneousFiles = array();
	
	/**
	 * Creates a new UploadHandler object.
	 * 
	 * @param	array<mixed>	$rawFileData
	 */
	protected function __construct(array $rawFileData) {
		if (is_array($rawFileData['name'])) {
			// multiple uploads
			for ($i = 0, $l = count($rawFileData['name']); $i < $l; $i++) {
				$this->files[] = new UploadFile($rawFileData['name'][$i], $rawFileData['tmp_name'][$i], $rawFileData['size'][$i], $rawFileData['error'][$i], ($rawFileData['tmp_name'][$i] ? (FileUtil::getMimeType($rawFileData['tmp_name'][$i]) ?: $rawFileData['type'][$i]) : ''));
			}
		}
		else {
			$this->files[] = new UploadFile($rawFileData['name'], $rawFileData['tmp_name'], $rawFileData['size'], $rawFileData['error'], ($rawFileData['tmp_name'] ? (FileUtil::getMimeType($rawFileData['tmp_name']) ?: $rawFileData['type']) : ''));
		}
	}
	
	/**
	 * Returns the list of uploaded files.
	 * 
	 * @return	array<wcf\system\upload\UploadFile>
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
	public function validateFiles(IUploadFileValidationStrategy $validationStrategy) {
		$result = true;
		foreach ($this->files as $file) {
			if (!$validationStrategy->validate($file)) {
				$this->erroneousFiles[] = $file;
				$result = false;
			}
		}
		
		return $result;
	}
	
	/**
	 * Returns a list of erroneous files.
	 * 
	 * @return	array<wcf\system\upload\UploadFile>
	 */
	public function getErroneousFiles() {
		return $this->erroneousFiles;
	}
	
	/**
	 * Saves the uploaded files.
	 * 
	 * @param	wcf\system\upload\IUploadFileSaveStrategy	$saveStrategy
	 */
	public function saveFiles(IUploadFileSaveStrategy $saveStrategy) {
		foreach ($this->files as $file) {
			if (!$file->getValidationErrorType()) {
				$saveStrategy->save($file);
			}
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
