<?php
namespace wcf\system\option;
use wcf\data\option\Option;
use wcf\data\package\PackageCache;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\upload\IUploadFileValidationStrategy;
use wcf\system\upload\UploadHandler;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * Option type implementation for uploading a file.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Option
 */
class FileOptionType extends AbstractOptionType {
	/**
	 * upload handler for option files
	 * @var	UploadHandler[]
	 */
	protected $uploadHandlers = [];
	
	/**
	 * Creates the upload handler for the given option.
	 * 
	 * @param	\wcf\data\option\Option		$option
	 */
	protected function createUploadHandler(Option $option) {
		if (!isset($this->uploadHandlers[$option->optionName])) {
			$this->uploadHandlers[$option->optionName] = UploadHandler::getUploadHandler($option->optionName);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function getData(Option $option, $newValue) {
		$this->createUploadHandler($option);
		if ($this->uploadHandlers[$option->optionName] === null) {
			return '';
		}
		
		$files = $this->uploadHandlers[$option->optionName]->getFiles();
		$file = reset($files);
		
		// check if file has been uploaded
		if (!$file->getFilename()) {
			// if checkbox is checked, remove file
			if ($newValue) {
				@unlink($option->optionValue);
				
				return '';
			}
			
			// use old value
			return $option->optionValue;
		}
		else if ($option->optionValue) {
			// delete old file first
			@unlink($option->optionValue);
		}
		
		// determine location the file will be stored at
		$package = PackageCache::getInstance()->getPackage($option->packageID);
		$fileLocation = FileUtil::addTrailingSlash(FileUtil::getRealPath(WCF_DIR.$package->packageDir)).$option->filelocation.'.'.$file->getFileExtension();
		
		// save file
		move_uploaded_file($file->getLocation(), $fileLocation);
		
		// return file location as the value to store in the database
		return $fileLocation;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getFormElement(Option $option, $value) {
		WCF::getTPL()->assign([
			'option' => $option,
			'value' => $value
		]);
		
		return WCF::getTPL()->fetch('fileOptionType');
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate(Option $option, $newValue) {
		$this->createUploadHandler($option);
		if ($this->uploadHandlers[$option->optionName] === null) {
			return;
		}
		
		$files = $this->uploadHandlers[$option->optionName]->getFiles();
		$file = reset($files);
		
		// check if file has been uploaded
		if (!$file->getFilename()) {
			return;
		}
		
		// validate file
		if ($option->filevalidation) {
			$fileValidation = new $option->filevalidation();
			if (!($fileValidation instanceof IUploadFileValidationStrategy)) {
				throw new SystemException("The file validation class needs to implement '".IUploadFileValidationStrategy::class."'");
			}
			
			if (!$this->uploadHandlers[$option->optionName]->validateFiles($fileValidation)) {
				$erroneousFiles = $this->uploadHandlers[$option->optionName]->getErroneousFiles();
				$erroneousFile = reset($erroneousFiles);
				
				throw new UserInputException($option->optionName, 'file.'.$erroneousFile->getValidationErrorType());
			}
		}
	}
}
