<?php
namespace wcf\system\upload;
use wcf\util\FileUtil;

/**
 * Handles file uploads.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Upload
 */
class UploadHandler {
	/**
	 * list of uploaded files
	 * @var	UploadFile[]
	 */
	protected $files = [];
	
	/**
	 * list of validation errors.
	 * @var	array
	 */
	protected $erroneousFiles = [];
	
	/**
	 * Creates a new UploadHandler object.
	 * 
	 * @param	mixed[]		$rawFileData
	 */
	protected function __construct(array $rawFileData) {
		if (is_array($rawFileData['name'])) {
			// iOS work-around
			$newRawFileData = [
				'name' => [],
				'type' => [],
				'tmp_name' => [],
				'error' => [],
				'size' => []
			];
			$i = 0;
			foreach (array_keys($rawFileData['name']) as $internalFileID) {
				$newRawFileData['name'][$i] = '__wcf_' . $internalFileID . '_' . $rawFileData['name'][$internalFileID]; // __wcf_X_filename.ext
				$newRawFileData['type'][$i] = $rawFileData['type'][$internalFileID];
				$newRawFileData['tmp_name'][$i] = $rawFileData['tmp_name'][$internalFileID];
				$newRawFileData['error'][$i] = $rawFileData['error'][$internalFileID];
				$newRawFileData['size'][$i] = $rawFileData['size'][$internalFileID];
				
				$i++;
			}
			$rawFileData = $newRawFileData;
			
			// multiple uploads
			for ($i = 0, $l = count($rawFileData['name']); $i < $l; $i++) {
				$this->files[] = new UploadFile($rawFileData['name'][$i], $rawFileData['tmp_name'][$i], $rawFileData['size'][$i], $rawFileData['error'][$i], ($rawFileData['tmp_name'][$i] ? (self::getMimeType($rawFileData['tmp_name'][$i], $rawFileData['type'][$i])) : ''));
			}
		}
		else {
			$this->files[] = new UploadFile($rawFileData['name'], $rawFileData['tmp_name'], $rawFileData['size'], $rawFileData['error'], ($rawFileData['tmp_name'] ? (self::getMimeType($rawFileData['tmp_name'], $rawFileData['type'])) : ''));
		}
	}
	
	/**
	 * Returns the list of uploaded files.
	 * 
	 * @return	UploadFile[]
	 */
	public function getFiles() {
		return $this->files;
	}
	
	/**
	 * Validates the uploaded files. Returns true on success, otherwise false.
	 * 
	 * @param	IUploadFileValidationStrategy	$validationStrategy
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
	 * @return	UploadFile[]
	 */
	public function getErroneousFiles() {
		return $this->erroneousFiles;
	}
	
	/**
	 * Saves the uploaded files.
	 * 
	 * @param	\wcf\system\upload\IUploadFileSaveStrategy	$saveStrategy
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
	 * @return	\wcf\system\upload\UploadHandler
	 */
	public static function getUploadHandler($identifier) {
		if (isset($_FILES[$identifier]) && is_array($_FILES[$identifier])) return new UploadHandler($_FILES[$identifier]);
		
		return null;
	}
	
	/**
	 * Returns the mime type of a file.
	 * 
	 * @param	string		$file
	 * @param	string		$mimeType	mime type transferred by client
	 * @return	string
	 */
	protected static function getMimeType($file, $mimeType) {
		if ($mimeType == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' || $mimeType == 'application/vnd.openxmlformats-officedocument.presentationml.presentation' || $mimeType == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
			// libmagic can not detect mime type of docx, xlsx and pttx files
			return $mimeType;
		}
		
		$finfoMimeType = FileUtil::getMimeType($file);
		if ($finfoMimeType) return $finfoMimeType;
		
		return $mimeType;
	}
}
