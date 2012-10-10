<?php
namespace wcf\data\style;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\image\ImageHandler;
use wcf\system\style\StyleHandler;
use wcf\system\upload\DefaultUploadFileValidationStrategy;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * Executes style-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.style
 * @category 	Community Framework
 */
class StyleAction extends AbstractDatabaseObjectAction {
	/**
	 * @see wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\style\StyleEditor';
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::create()
	 */
	public function create() {
		$style = parent::create();
		
		// add variables
		$this->updateVariables($style);
		
		// handle style preview image
		$this->updateStylePreviewImage($style);
		
		return $style;
	}
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::update()
	 */
	public function update() {
		parent::update();
		
		foreach ($this->objects as $style) {
			// update variables
			$this->updateVariables($style->getDecoratedObject(), true);
			
			// handle style preview image
			$this->updateStylePreviewImage($style->getDecoratedObject());
			
			// reset stylesheet
			StyleHandler::getInstance()->resetStylesheet($style->getDecoratedObject());
		}
	}
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::delete()
	 */
	public function delete() {
		$count = parent::delete();
		
		foreach ($this->objects as $style) {
			// remove custom icons
			if ($style->iconPath && $style->iconPath != 'icon/') {
				$this->removeDirectory($style->iconPath);
			}
			
			// remove custom images
			if ($style->imagePath && $style->imagePath != 'images/') {
				$this->removeDirectory($style->imagePath);
			}
			
			// remove preview image
			$previewImage = WCF_DIR.'images/'.$style->image;
			if (file_exists($previewImage)) {
				@unlink($previewImage);
			}
			
			// remove stylesheet
			StyleHandler::getInstance()->resetStylesheet($style->getDecoratedObject());
		}
		
		return $count;
	}
	
	/**
	 * Recursively removes a directory and all it's contents.
	 * 
	 * @param	string		$pathComponent
	 */
	protected function removeDirectory($pathComponent) {
		$dir = WCF_DIR.$pathComponent;
		if (is_dir($dir)) {
			$iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir), \RecursiveIteratorIterator::CHILD_FIRST);
			foreach ($iterator as $path) {
				if ($path->isDir()) {
					@rmdir($path->__toString());
				}
				else {
					@unlink($path->__toString());
				}
			}
			
			@rmdir($dir);
		}
	}
	
	/**
	 * Updates style variables for given style.
	 * 
	 * @param	wcf\data\style\Style	$style
	 * @param	boolean			$removePreviousVariables
	 */
	protected function updateVariables(Style $style, $removePreviousVariables = false) {
		if (!isset($this->parameters['variables']) || !is_array($this->parameters['variables'])) {
			return;
		}
		
		$sql = "SELECT	variableID, variableName, defaultValue
			FROM	wcf".WCF_N."_style_variable";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		$variables = array();
		while ($row = $statement->fetchArray()) {
			$variableName = $row['variableName'];
				
			// ignore variables with identical value
			if (isset($this->parameters['variables'][$variableName])) {
				if ($this->parameters['variables'][$variableName] == $row['defaultValue']) {
					continue;
				}
				else {
					$variables[$row['variableID']] = $this->parameters['variables'][$variableName];
				}
			}
		}
			
		// remove previously set variables
		if ($removePreviousVariables) {
			$sql = "DELETE FROM	wcf".WCF_N."_style_variable_value
				WHERE		styleID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array($style->styleID));
		}
			
		// insert variables that differ from default values
		if (!empty($variables)) {
			$sql = "INSERT INTO	wcf".WCF_N."_style_variable_value
						(styleID, variableID, variableValue)
				VALUES		(?, ?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
				
			WCF::getDB()->beginTransaction();
			foreach ($variables as $variableID => $variableValue) {
				$statement->execute(array(
					$style->styleID,
					$variableID,
					$variableValue
				));
			}
			WCF::getDB()->commitTransaction();
		}
	}
	
	/**
	 * Updates style preview image.
	 * 
	 * @param	wcf\data\style\Style	$style
	 */
	protected function updateStylePreviewImage(Style $style) {
		if (!isset($this->parameters['tmpHash'])) {
			return;
		}
		
		$fileExtension = WCF::getSession()->getVar('stylePreview-'.$this->parameters['tmpHash']);
		if ($fileExtension !== null) {
			$oldFilename = WCF_DIR.'images/stylePreview-'.$this->parameters['tmpHash'].'.'.$fileExtension;
			if (file_exists($oldFilename)) {
				$filename = 'stylePreview-'.$style->styleID.'.'.$fileExtension;
				if (@rename($oldFilename, WCF_DIR.'images/'.$filename)) {
					// delete old file if it has a different file extension
					if ($style->image != $filename) {
						@unlink(WCF_DIR.'images/'.$style->image);
						
						// update filename in database
						$sql = "UPDATE	wcf".WCF_N."_style
							SET	image = ?
							WHERE	styleID = ?";
						$statement = WCF::getDB()->prepareStatement($sql);
						$statement->execute(array(
							$filename,
							$style->styleID
						));
					}
				}
				else {
					// remove temp file
					@unlink($oldFilename);
				}
			}
		}
	}
	
	/**
	 * Validates the upload action.
	 */
	public function validateUpload() {
		// check upload permissions
		if (!WCF::getSession()->getPermission('admin.style.canAddStyle')) {
			throw new PermissionDeniedException();
		}
		
		if (!isset($this->parameters['tmpHash']) || empty($this->parameters['tmpHash'])) {
			throw new UserInputException('tmpHash');
		}
		
		if (count($this->parameters['__files']->getFiles()) != 1) {
			throw new IllegalLinkException();
		}
		
		// check max filesize, allowed file extensions etc.
		$this->parameters['__files']->validateFiles(new DefaultUploadFileValidationStrategy(PHP_INT_MAX, array('jpg', 'jpeg', 'png')));
	}
	
	/**
	 * Handles uploaded preview images.
	 * 
	 * @return	array<string>
	 */
	public function upload() {
		// save files
		$files = $this->parameters['__files']->getFiles();
		$file = $files[0];
		
		try {
			if (!$file->getValidationErrorType()) {
				// shrink avatar if necessary
				$fileLocation = $file->getLocation();
				$imageData = getimagesize($fileLocation);
				if ($imageData[0] > Style::PREVIEW_IMAGE_MAX_WIDTH || $imageData[1] > Style::PREVIEW_IMAGE_MAX_HEIGHT) {
					try {
						$adapter = ImageHandler::getInstance()->getAdapter();
						$adapter->loadFile($fileLocation);
						$fileLocation = FileUtil::getTemporaryFilename();
						$thumbnail = $adapter->createThumbnail(Style::PREVIEW_IMAGE_MAX_WIDTH, Style::PREVIEW_IMAGE_MAX_HEIGHT, false);
						$adapter->writeImage($thumbnail, $fileLocation);
						$imageData = getimagesize($fileLocation);
					}
					catch (SystemException $e) {
						throw new UserInputException('image');
					}
				}
				
				// move uploaded file
				if (@copy($fileLocation, WCF_DIR.'images/stylePreview-'.$this->parameters['tmpHash'].'.'.$file->getFileExtension())) {
					@unlink($fileLocation);
					
					// store extension within session variables
					WCF::getSession()->register('stylePreview-'.$this->parameters['tmpHash'], $file->getFileExtension());
					
					// return result
					return array(
						'errorType' => '',
						'url' => WCF::getPath().'images/stylePreview-'.$this->parameters['tmpHash'].'.'.$file->getFileExtension()
					);
				}
				else {
					throw new UserInputException('image', 'uploadFailed');
				}
			}
		}
		catch (UserInputException $e) {
			$file->setValidationErrorType($e->getType());
		}
		
		return array('errorType' => $file->getValidationErrorType());
	}
}
