<?php
namespace wcf\system\file\upload;
use wcf\system\exception\ImplementationException;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Handles uploads for files.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2018 WoltLab GmbH
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
	 * Contains the registered upload fields. 
	 * 
	 * @var UploadField[]
	 */
	protected $fields = [];
	
	/**
	 * Registers a UploadField.
	 * 
	 * @param       UploadField     $field
	 */
	public function registerUploadField(UploadField $field) {
		if (isset($this->fields[$field->getFieldId()])) {
			throw new \InvalidArgumentException('UploadField with the id "'. $field->getFieldId() .'" is already registered.');
		}
		
		// read internal identifier
		if (!empty($_POST) && isset($_POST[$field->getFieldId()]) && $this->isValidInternalId($_POST[$field->getFieldId()])) {
			$field->setInternalId($_POST[$field->getFieldId()]);
			
			$this->fields[$field->getFieldId()] = $field;
		}
		else {
			do {
				$internalId = bin2hex(random_bytes(32));
			} 
			while (in_array($internalId, $this->getKnownInternalIds()));
			
			$field->setInternalId($internalId);
			
			$this->registerFieldInStorage($field);
		}
	}
	
	/**
	 * Returns the uploaded files for a specific fieldId.
	 * 
	 * @param       string          $fieldId
	 * @return      UploadFile[]
	 */
	public function getFilesForFieldId($fieldId) {
		if (!isset($this->fields[$fieldId])) {
			throw new \InvalidArgumentException('UploadField with the id "'. $fieldId .'" is unknown.');
		}
		
		return $this->getFilesForInternalId($this->fields[$fieldId]->getInternalId());
	}
	
	/**
	 * Returns the removed but previosly proccessed files for a specific fieldId.
	 *
	 * @param       string          $fieldId
	 * @param       boolean         $processFiles
	 * @return      UploadFile[]
	 */
	public function getRemovedFiledForFieldId($fieldId, $processFiles = true) {
		if (!isset($this->fields[$fieldId])) {
			throw new \InvalidArgumentException('UploadField with the id "'. $fieldId .'" is unknown.');
		}
		
		return $this->getRemovedFiledForInternalId($this->fields[$fieldId]->getInternalId(), $processFiles);
	}
	
	/**
	 * Returns the removed but previosly proccessed files for a specific internalId.
	 *
	 * @param       string          $internalId
	 * @param       boolean         $processFiles
	 * @return      UploadFile[]
	 */
	public function getRemovedFiledForInternalId($internalId, $processFiles = true) {
		if (isset($this->getStorage()[$internalId])) {
			$files = $this->getStorage()[$internalId]['removedFiles'];
			$removedFiles = [];
			
			/** @var UploadFile $file */
			foreach ($files as $file) {
				if (file_exists($file->getLocation())) {
					$removedFiles[] = $file;
				}
			}
			
			if ($processFiles) $this->processRemovedFiles($this->getFieldForInternalId($internalId));
			
			return $removedFiles;
		}
		
		return [];
	}
	
	/**
	 * @param $internalId
	 * @param $uniqueFileId
	 */
	public function removeFile($internalId, $uniqueFileId) {
		if (!$this->isValidInternalId($internalId)) {
			throw new \InvalidArgumentException('InternalId "'. $internalId .'" is unknown.');
		}
		
		$file = $this->getFileForUniqueFileId($internalId, $uniqueFileId);
		
		if ($file === null) {
			return; 
		}		
		
		$storage = $this->getStorage();
		
		if ($file->isProcessed()) {
			$storage[$internalId]['removedFiles'] = array_unique(array_merge($storage[$internalId]['removedFiles'], [$file]));
		}
		else {
			@unlink($file->getLocation());
		}
		
		/** @var UploadFile $storageFile */
		foreach ($storage[$internalId]['files'] as $id => $storageFile) {
			if ($storageFile->getUniqueFileId() === $uniqueFileId) {
				unset($storage[$internalId]['files'][$id]);
				break;
			}
		}
		
		WCF::getSession()->register(self::UPLOAD_HANDLER_SESSION_VAR, $storage);
	}
	
	/**
	 * Renders the field with the given fieldId for the template.
	 * @param       string          $fieldId
	 * @return      string
	 */
	public function renderField($fieldId) {
		if (!isset($this->fields[$fieldId])) {
			throw new \InvalidArgumentException('UploadField with the id "'. $fieldId .'" is unknown.');
		}
		
		return WCF::getTPL()->fetch('uploadFieldComponent', 'wcf', [
			'field' => $this->fields[$fieldId], 
			'fieldId' => $fieldId,
			'files' => $this->getFilesForFieldId($fieldId)
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
	 * 
	 * @param       string        $internalId
	 * @param       string        $uniqueFileId
	 * @return      boolean
	 */
	public function isValidUniqueFileId($internalId, $uniqueFileId) {
		return $this->getFileForUniqueFileId($internalId, $uniqueFileId) !== null; 
	}
	
	/**
	 *
	 * @param       string          $internalId
	 * @param       string          $uniqueFileId
	 * @return      UploadFile|null
	 */
	public function getFileForUniqueFileId($internalId, $uniqueFileId) {
		if (!$this->isValidInternalId($internalId)) {
			throw new \InvalidArgumentException('InternalId "'. $internalId .'" is unknown.');
		}
		
		foreach ($this->getFilesForInternalId($internalId) as $file) {
			if ($file->getUniqueFileId() === $uniqueFileId) {
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
	public function addFileForInternalId($internalId, UploadFile $file) {
		$this->registerFilesForInternalId($internalId, array_merge($this->getFilesForInternalId($internalId), [$file]));
	}
	
	/**
	 * Registers files for the given internalId.
	 *
	 * HEADS UP: Deletes all uploaded files and overwrites them with
	 * the given files. If you want to add a file, use the addFileForInternalId method. 
	 * 
	 * @param       string          $internalId
	 * @param       UploadFile[]    $files
	 */
	public function registerFilesForInternalId($internalId, array $files) {
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
	public function addFileForField($fieldId, UploadFile $file) {
		$this->registerFilesForField($fieldId, array_merge($this->getFilesForFieldId($fieldId), [$file]));
	}
	
	/**
	 * Register files for the field with the given fieldId.
	 * 
	 * HEADS UP: Deletes all uploaded files and overwrites them with
	 * the given files. If you want to add a file, use the addFileForField method.
	 * 
	 * @param       string          $fieldId
	 * @param       UploadFile[]    $files
	 */
	public function registerFilesForField($fieldId, array $files) {
		if (!isset($this->fields[$fieldId])) {
			throw new \InvalidArgumentException('UploadField with the id "'. $fieldId .'" is unknown.');
		}
		
		$this->registerFilesForInternalId($this->fields[$fieldId]->getInternalId(), $files);
	}
	
	/**
	 * Returns the field for the internalId.
	 * 
	 * @param       string          $internalId
	 * @return      UploadField
	 */
	public function getFieldForInternalId($internalId) {
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
	public function getFilesCountForInternalId($internalId) {
		return count($this->getFilesForInternalId($internalId));
	}
	
	/**
	 * Returns the files for an internal identifier.
	 *
	 * @param       string          $internalId
	 * @return      UploadFile[]
	 */
	private function getFilesForInternalId($internalId) {
		if (isset($this->getStorage()[$internalId])) {
			$files = $this->getStorage()[$internalId]['files'];
			
			// check avaibility of the files 
			/** @var UploadFile $file */
			foreach ($files as $file) {
				if (!file_exists($file->getLocation())) {
					$this->removeFile($internalId, $file->getUniqueFileId());
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
	 * @param UploadField $field
	 */
	private function processRemovedFiles(UploadField $field) {
		$storage = $this->getStorage();
		$storage[$field->getInternalId()]['removedFiles'] = [];
		
		WCF::getSession()->register(self::UPLOAD_HANDLER_SESSION_VAR, $storage);
	}
	
	/**
	 * Returns the known internalIds. 
	 * 
	 * @return string[]
	 */
	private function getKnownInternalIds() {
		return array_keys($this->getStorage());
	}
}
