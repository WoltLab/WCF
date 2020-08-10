<?php
namespace wcf\system\file\upload;
use wcf\system\exception\ImplementationException;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\ImageUtil;
use wcf\util\StringUtil;

/**
 * Handles uploads for files.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\File\Upload
 * @since       5.2
 */
class UploadHandler extends SingletonFactory {
	/**
	 * Session variable name for the file storage.
	 * @var string
	 */
	const UPLOAD_HANDLER_SESSION_VAR = 'file_upload_handler_storage';
	
	/**
	 * Contains the valid image extensions w/o svg.
	 * @var string
	 * @deprecated 5.3 Use \wcf\util\ImageUtil::$imageExtensions instead (direct replacement).
	 */
	const VALID_IMAGE_EXTENSIONS = ['jpeg', 'jpg', 'png', 'gif'];
	
	/**
	 * Contains the registered upload fields. 
	 * 
	 * @var UploadField[]
	 */
	protected $fields = [];
	
	/**
	 * Registers a UploadField.
	 * 
	 * @param       UploadField     $field
	 * @param       mixed           $requestData
	 * 
	 * @throws      \InvalidArgumentException       if a field with the given fieldId is already registered
	 */
	public function registerUploadField(UploadField $field, array $requestData = null) {
		if (isset($this->fields[$field->getFieldId()])) {
			throw new \InvalidArgumentException('UploadField with the id "'. $field->getFieldId() .'" is already registered.');
		}
		
		if ($requestData === null) {
			$requestData = $_POST;
		}
		
		// read internal identifier
		if (!empty($requestData) && isset($requestData[$field->getFieldId()]) && $this->isValidInternalId($requestData[$field->getFieldId()])) {
			$field->setInternalId($requestData[$field->getFieldId()]);
			
			$this->fields[$field->getFieldId()] = $field;
		}
		else {
			$internalId = StringUtil::getRandomID();
			
			$field->setInternalId($internalId);
			
			$this->registerFieldInStorage($field);
		}
	}
	
	/**
	 * Unregisters an upload field by the given field id.
	 * 
	 * @param       string          $fieldId
	 * 
	 * @throws      \InvalidArgumentException       if the given fieldId is unknown
	 */
	public function unregisterUploadField($fieldId) {
		if (!isset($this->fields[$fieldId])) {
			throw new \InvalidArgumentException('UploadField with the id "'. $fieldId .'" is unknown.');
		}
		
		$storage = $this->getStorage();
		unset($storage[$this->fields[$fieldId]->getInternalId()]);
		
		WCF::getSession()->register(self::UPLOAD_HANDLER_SESSION_VAR, $storage);
		
		unset($this->fields[$fieldId]);
	}
	
	/**
	 * Returns the uploaded files for a specific fieldId.
	 * 
	 * @param       string          $fieldId
	 * @return      UploadFile[]
	 * 
	 * @throws      \InvalidArgumentException       if the given fieldId is unknown
	 */
	public function getFilesByFieldId($fieldId) {
		if (!isset($this->fields[$fieldId])) {
			throw new \InvalidArgumentException('UploadField with the id "'. $fieldId .'" is unknown.');
		}
		
		return $this->getFilesByInternalId($this->fields[$fieldId]->getInternalId());
	}
	
	/**
	 * Returns the removed but previosly proccessed files for a specific fieldId.
	 *
	 * @param       string          $fieldId
	 * @param       boolean         $processFiles
	 * @return      UploadFile[]
	 * 
	 * @throws      \InvalidArgumentException       if the given fieldId is unknown
	 */
	public function getRemovedFiledByFieldId($fieldId, $processFiles = true) {
		if (!isset($this->fields[$fieldId])) {
			throw new \InvalidArgumentException('UploadField with the id "'. $fieldId .'" is unknown.');
		}
		
		return $this->getRemovedFiledByInternalId($this->fields[$fieldId]->getInternalId(), $processFiles);
	}
	
	/**
	 * Returns the removed but previosly proccessed files for a specific internalId.
	 *
	 * @param       string          $internalId
	 * @param       boolean         $processFiles
	 * @return      UploadFile[]
	 */
	public function getRemovedFiledByInternalId($internalId, $processFiles = true) {
		if (isset($this->getStorage()[$internalId])) {
			$files = $this->getStorage()[$internalId]['removedFiles'];
			$removedFiles = [];
			
			/** @var UploadFile $file */
			foreach ($files as $file) {
				if (file_exists($file->getLocation())) {
					$removedFiles[] = $file;
				}
			}
			
			if ($processFiles) $this->processRemovedFiles($this->getFieldByInternalId($internalId));
			
			return $removedFiles;
		}
		
		return [];
	}
	
	/**
	 * Removes a file from the upload. 
	 * 
	 * @param       string          $internalId
	 * @param       string          $uniqueFileId
	 * 
	 * @throws      \InvalidArgumentException       if the given internalId is unknown
	 */
	public function removeFile($internalId, $uniqueFileId) {
		if (!$this->isValidInternalId($internalId)) {
			throw new \InvalidArgumentException('InternalId "'. $internalId .'" is unknown.');
		}
		
		$file = $this->getFileByUniqueFileId($internalId, $uniqueFileId);
		
		if ($file === null) {
			return; 
		}		
		
		$this->removeFileByObject($internalId, $file);
	}
	
	/**
	 * Removes an file by file object. 
	 * 
	 * @param       string          $internalId
	 * @param       UploadFile      $file
	 */
	private function removeFileByObject($internalId, UploadFile $file) {
		$storage = $this->getStorage();
		
		if ($file->isProcessed()) {
			$storage[$internalId]['removedFiles'] = array_merge($storage[$internalId]['removedFiles'], [$file]);
		}
		else {
			@unlink($file->getLocation());
		}
		
		/** @var UploadFile $storageFile */
		foreach ($storage[$internalId]['files'] as $id => $storageFile) {
			if ($storageFile->getUniqueFileId() === $file->getUniqueFileId()) {
				unset($storage[$internalId]['files'][$id]);
				break;
			}
		}
		
		WCF::getSession()->register(self::UPLOAD_HANDLER_SESSION_VAR, $storage);
	}
	
	/**
	 * Renders the field with the given fieldId for the template.
	 * 
	 * @param       string          $fieldId
	 * @return      string
	 * 
	 * @throws      \InvalidArgumentException       if the given fieldId is unknown
	 */
	public function renderField($fieldId) {
		if (!isset($this->fields[$fieldId])) {
			throw new \InvalidArgumentException('UploadField with the id "'. $fieldId .'" is unknown.');
		}
		
		return WCF::getTPL()->fetch('uploadFieldComponent', 'wcf', [
			'uploadField' => $this->fields[$fieldId], 
			'uploadFieldId' => $fieldId,
			'uploadFieldFiles' => $this->getFilesByFieldId($fieldId)
		]);
	}
	
	/**
	 * Returns true, if the given internalId is valid.
	 * 
	 * @param       string          $internalId
	 * @return      boolean
	 */
	public function isValidInternalId($internalId) {
		return isset($this->getStorage()[$internalId]); 
	}
	
	/**
	 * Checks whether the passed internal file id is valid for an internal id.
	 * 
	 * @param       string        $internalId
	 * @param       string        $uniqueFileId
	 * @return      boolean
	 */
	public function isValidUniqueFileId($internalId, $uniqueFileId) {
		return $this->getFileByUniqueFileId($internalId, $uniqueFileId) !== null; 
	}
	
	/**
	 * Return all files by file id. 
	 * 
	 * @param       string          $internalId
	 * @param       string          $uniqueFileId
	 * @return      UploadFile|null
	 * 
	 * @throws      \InvalidArgumentException       if the given internalId is unknown
	 */
	public function getFileByUniqueFileId($internalId, $uniqueFileId) {
		if (!$this->isValidInternalId($internalId)) {
			throw new \InvalidArgumentException('InternalId "'. $internalId .'" is unknown.');
		}
		
		foreach ($this->getFilesByInternalId($internalId) as $file) {
			if (hash_equals($file->getUniqueFileId(), $uniqueFileId)) {
				return $file;
			}
		}
		
		return null;
	} 
	
	/**
	 * Add a file for an internalId. 
	 * 
	 * @param       string          $internalId
	 * @param       UploadFile      $file
	 */
	public function addFileByInternalId($internalId, UploadFile $file) {
		$this->registerFilesByInternalId($internalId, array_merge($this->getFilesByInternalId($internalId), [$file]));
	}
	
	/**
	 * Registers files for the given internalId.
	 *
	 * HEADS UP: Deletes all uploaded files and overwrites them with
	 * the given files. If you want to add a file, use the addFileForInternalId method. 
	 * 
	 * @param       string          $internalId
	 * @param       UploadFile[]    $files
	 * 
	 * @throws      \InvalidArgumentException       if the given internalId is unknown
	 */
	public function registerFilesByInternalId($internalId, array $files) {
		if (!$this->isValidInternalId($internalId)) {
			throw new \InvalidArgumentException('InternalId "'. $internalId .'" is unknown.');
		}
		
		foreach ($files as $file) {
			if (!($file instanceof UploadFile)) {
				throw new ImplementationException(get_class($file), UploadFile::class);
			}
		}
		
		$storage = $this->getStorage();
		$storage[$internalId]['files'] = $files;
		
		WCF::getSession()->register(self::UPLOAD_HANDLER_SESSION_VAR, $storage);
	}
	
	/**
	 * Add a file for an upload field with the given fieldId.
	 * 
	 * @param       string          $fieldId
	 * @param       UploadFile      $file
	 */
	public function addFileByField($fieldId, UploadFile $file) {
		$this->registerFilesByField($fieldId, array_merge($this->getFilesByFieldId($fieldId), [$file]));
	}
	
	/**
	 * Register files for the field with the given fieldId.
	 * 
	 * HEADS UP: Deletes all uploaded files and overwrites them with
	 * the given files. If you want to add a file, use the addFileForField method.
	 * 
	 * @param       string          $fieldId
	 * @param       UploadFile[]    $files
	 * 
	 * @throws      \InvalidArgumentException       if the given fieldId is unknown
	 */
	public function registerFilesByField($fieldId, array $files) {
		if (!isset($this->fields[$fieldId])) {
			throw new \InvalidArgumentException('UploadField with the id "'. $fieldId .'" is unknown.');
		}
		
		$this->registerFilesByInternalId($this->fields[$fieldId]->getInternalId(), $files);
	}
	
	/**
	 * Returns the field for the internalId.
	 * 
	 * @param       string          $internalId
	 * @return      UploadField
	 * 
	 * @throws      \InvalidArgumentException       if the given internalId is unknown
	 */
	public function getFieldByInternalId($internalId) {
		if (!$this->isValidInternalId($internalId)) {
			throw new \InvalidArgumentException('InternalId "'. $internalId .'" is unknown.');
		}
		
		return $this->getStorage()[$internalId]['field'];
	}
	
	/**
	 * Returns the count of uploaded files for an internal id. 
	 * 
	 * @param       string          $internalId
	 * @return      int
	 */
	public function getFilesCountByInternalId($internalId) {
		return count($this->getFilesByInternalId($internalId));
	}
	
	/**
	 * Returns true, iff a field with the given fieldId is already registered. 
	 * 
	 * @param       string          $fieldId
	 * @return      boolean
	 */
	public function isRegisteredFieldId($fieldId) {
		return isset($this->fields[$fieldId]);
	}
	
	/**
	 * Returns the files for an internal identifier.
	 *
	 * @param       string          $internalId
	 * @return      UploadFile[]
	 */
	private function getFilesByInternalId($internalId) {
		if (isset($this->getStorage()[$internalId])) {
			$files = $this->getStorage()[$internalId]['files'];
			
			// check availability of the files 
			/** @var UploadFile $file */
			foreach ($files as $file) {
				if (!file_exists($file->getLocation())) {
					$this->removeFileByObject($internalId, $file);
				}
			}
			
			return $files;
		}
		
		return [];
	}
	
	/**
	 * Returns the upload handler storage, located in the session var.
	 * 
	 * @return array
	 */
	private function getStorage() {
		if (!is_array(WCF::getSession()->getVar(self::UPLOAD_HANDLER_SESSION_VAR))) {
			return [];
		}
		
		return WCF::getSession()->getVar(self::UPLOAD_HANDLER_SESSION_VAR);
	}
	
	/**
	 * Registers an field in the storage. 
	 * 
	 * @param       UploadField     $field
	 */
	private function registerFieldInStorage(UploadField $field) {
		$storage = $this->getStorage();
		$storage[$field->getInternalId()] = [
			'field' => $field, 
			'files' => [],
			'removedFiles' => []
		];
		
		$this->fields[$field->getFieldId()] = $field;
		
		WCF::getSession()->register(self::UPLOAD_HANDLER_SESSION_VAR, $storage);
	}
	
	/**
	 * Remove the removedFiles from the upload process.
	 * 
	 * @param       UploadField     $field
	 */
	private function processRemovedFiles(UploadField $field) {
		$storage = $this->getStorage();
		$storage[$field->getInternalId()]['removedFiles'] = [];
		
		WCF::getSession()->register(self::UPLOAD_HANDLER_SESSION_VAR, $storage);
	}
	
	/**
	 * Returns true, iff the given location contains an image. 
	 * 
	 * @param       string          $location
	 * @param       string          $imageName
	 * @param       bool            $svgImageAllowed
	 * @return      bool
	 * @deprecated  5.3 Use \wcf\util\ImageUtil::isImage() instead (direct replacement).
	 */
	public static function isValidImage($location, $imageName, $svgImageAllowed) {
		return ImageUtil::isImage($location, $imageName, $svgImageAllowed);
	}
}
