<?php
namespace wcf\data\style;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IToggleAction;
use wcf\system\cache\builder\StyleCacheBuilder;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\image\ImageHandler;
use wcf\system\request\LinkHandler;
use wcf\system\style\StyleHandler;
use wcf\system\upload\DefaultUploadFileValidationStrategy;
use wcf\system\Regex;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * Executes style-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.style
 * @category	Community Framework
 */
class StyleAction extends AbstractDatabaseObjectAction implements IToggleAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$allowGuestAccess
	 */
	protected $allowGuestAccess = array('changeStyle', 'getStyleChooser');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\style\StyleEditor';
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$permissionsDelete
	 */
	protected $permissionsDelete = array('admin.style.canManageStyle');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$permissionsUpdate
	 */
	protected $permissionsUpdate = array('admin.style.canManageStyle');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$requireACP
	 */
	protected $requireACP = array('copy', 'delete', 'setAsDefault', 'toggle', 'update', 'upload', 'uploadLogo');
	
	/**
	 * style object
	 * @var	\wcf\data\style\Style
	 */
	public $style = null;
	
	/**
	 * style editor object
	 * @var	\wcf\data\style\StyleEditor
	 */
	public $styleEditor = null;
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::create()
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
	 * @see	\wcf\data\AbstractDatabaseObjectAction::update()
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
	 * @see	\wcf\data\AbstractDatabaseObjectAction::delete()
	 */
	public function delete() {
		$count = parent::delete();
		
		foreach ($this->objects as $style) {
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
					@rmdir($path);
				}
				else {
					@unlink($path);
				}
			}
			
			@rmdir($dir);
		}
	}
	
	/**
	 * Updates style variables for given style.
	 * 
	 * @param	\wcf\data\style\Style	$style
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
	 * @param	\wcf\data\style\Style	$style
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
		if (!WCF::getSession()->getPermission('admin.style.canManageStyle')) {
			throw new PermissionDeniedException();
		}
		
		$this->readString('tmpHash');
		$this->readInteger('styleID', true);
		
		if ($this->parameters['styleID']) {
			$styles = StyleHandler::getInstance()->getStyles();
			if (!isset($styles[$this->parameters['styleID']])) {
				throw new UserInputException('styleID');
			}
			
			$this->style = $styles[$this->parameters['styleID']];
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
				// shrink preview image if necessary
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
					
					if ($this->parameters['styleID']) {
						$this->updateStylePreviewImage($this->style);
						
						return array(
							'url' => WCF::getPath().'images/stylePreview-'.$this->parameters['styleID'].'.'.$file->getFileExtension()
						);
					}
					
					// return result
					return array(
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
	
	/**
	 * Validates parameters to update a logo.
	 */
	public function validateUploadLogo() {
		$this->validateUpload();
	}
	
	/**
	 * Handles logo upload.
	 * 
	 * @return	array<string>
	 */
	public function uploadLogo() {
		// save files
		$files = $this->parameters['__files']->getFiles();
		$file = $files[0];
		
		try {
			if (!$file->getValidationErrorType()) {
				// shrink avatar if necessary
				$fileLocation = $file->getLocation();
				
				// move uploaded file
				if (@copy($fileLocation, WCF_DIR.'images/styleLogo-'.$this->parameters['tmpHash'].'.'.$file->getFileExtension())) {
					@unlink($fileLocation);
					
					// store extension within session variables
					WCF::getSession()->register('styleLogo-'.$this->parameters['tmpHash'], $file->getFileExtension());
					
					// return result
					return array(
						'url' => WCF::getPath().'images/styleLogo-'.$this->parameters['tmpHash'].'.'.$file->getFileExtension()
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
	
	/**
	 * Validates parameters to assign a new default style.
	 */
	public function validateSetAsDefault() {
		if (!WCF::getSession()->getPermission('admin.style.canManageStyle')) {
			throw new PermissionDeniedException();
		}
		
		if (empty($this->objects)) {
			$this->readObjects();
			if (empty($this->objects)) {
				throw new UserInputException('objectIDs');
			}
		}
		
		if (count($this->objects) > 1) {
			throw new UserInputException('objectIDs');
		}
	}
	
	/**
	 * Sets a style as new default style.
	 */
	public function setAsDefault() {
		$styleEditor = current($this->objects);
		$styleEditor->setAsDefault();
	}
	
	/**
	 * Validates parameters to copy a style.
	 */
	public function validateCopy() {
		if (!WCF::getSession()->getPermission('admin.style.canManageStyle')) {
			throw new PermissionDeniedException();
		}
		
		$this->styleEditor = $this->getSingleObject();
	}
	
	/**
	 * Copies a style.
	 * 
	 * @return	array<string>
	 */
	public function copy() {
		// get unique style name
		$sql = "SELECT	styleName
			FROM	wcf".WCF_N."_style
			WHERE	styleName LIKE ?
				AND styleID <> ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$this->styleEditor->styleName.'%',
			$this->styleEditor->styleID
		));
		$numbers = array();
		$regEx = new Regex('\((\d+)\)$');
		while ($row = $statement->fetchArray()) {
			$styleName = $row['styleName'];
			
			if ($regEx->match($styleName)) {
				$matches = $regEx->getMatches();
				
				// check if name matches the pattern 'styleName (x)'
				if ($styleName == $this->styleEditor->styleName . ' ('.$matches[1].')') {
					$numbers[] = $matches[1];
				}
			}
		}
		
		$number = (count($numbers)) ? max($numbers) + 1 : 2;
		$styleName = $this->styleEditor->styleName . ' ('.$number.')';
		
		// create the new style
		$newStyle = StyleEditor::create(array(
			'styleName' => $styleName,
			'templateGroupID' => $this->styleEditor->templateGroupID,
			'isDisabled' => 1, // newly created styles are disabled by default
			'styleDescription' => $this->styleEditor->styleDescription,
			'styleVersion' => $this->styleEditor->styleVersion,
			'styleDate' => $this->styleEditor->styleDate,
			'copyright' => $this->styleEditor->copyright,
			'license' => $this->styleEditor->license,
			'authorName' => $this->styleEditor->authorName,
			'authorURL' => $this->styleEditor->authorURL,
			'imagePath' => $this->styleEditor->imagePath
		));
		
		// check if style description uses i18n
		if (preg_match('~^wcf.style.styleDescription\d+$~', $newStyle->styleDescription)) {
			$styleDescription = 'wcf.style.styleDescription'.$newStyle->styleID;
			
			// copy language items
			$sql = "INSERT INTO	wcf".WCF_N."_language_item
						(languageID, languageItem, languageItemValue, languageItemOriginIsSystem, languageCategoryID, packageID)
				SELECT		languageID, '".$styleDescription."', languageItemValue, 0, languageCategoryID, packageID
				FROM		wcf".WCF_N."_language_item
				WHERE		languageItem = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array($newStyle->styleDescription));
			
			// update style description
			$styleEditor = new StyleEditor($newStyle);
			$styleEditor->update(array(
				'styleDescription' => $styleDescription
			));
		}
		
		// copy style variables
		$sql = "INSERT INTO	wcf".WCF_N."_style_variable_value
					(styleID, variableID, variableValue)
			SELECT		".$newStyle->styleID." AS styleID, value.variableID, value.variableValue
			FROM		wcf".WCF_N."_style_variable_value value
			WHERE		value.styleID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->styleEditor->styleID));
		
		// copy preview image
		if ($this->styleEditor->image) {
			// get extension
			$fileExtension = mb_substr($this->styleEditor->image, mb_strrpos($this->styleEditor->image, '.'));
			
			// copy existing preview image
			if (@copy(WCF_DIR.'images/'.$this->styleEditor->image, WCF_DIR.'images/stylePreview-'.$newStyle->styleID.$fileExtension)) {
				// bypass StyleEditor::update() to avoid scaling of already fitting image
				$sql = "UPDATE	wcf".WCF_N."_style
					SET	image = ?
					WHERE	styleID = ?";
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute(array(
					'stylePreview-'.$newStyle->styleID.$fileExtension,
					$newStyle->styleID
				));
			}
		}
		
		StyleCacheBuilder::getInstance()->reset();
		
		return array(
			'redirectURL' => LinkHandler::getInstance()->getLink('StyleEdit', array('id' => $newStyle->styleID))
		);
	}
	
	/**
	 * @see	\wcf\data\IToggleAction::validateToggle()
	 */
	public function validateToggle() {
		parent::validateUpdate();
		
		foreach ($this->objects as $style) {
			if ($style->isDefault) {
				throw new UserInputException('objectIDs');
			}
		}
	}
	
	/**
	 * @see	\wcf\data\IToggleAction::toggle()
	 */
	public function toggle() {
		foreach ($this->objects as $style) {
			$isDisabled = ($style->isDisabled) ? 0 : 1;
			$style->update(array('isDisabled' => $isDisabled));
		}
	}
	
	/**
	 * Validates parameters to change user style.
	 */
	public function validateChangeStyle() {
		$this->style = $this->getSingleObject();
		if ($this->style->isDisabled && !WCF::getSession()->getPermission('admin.style.canUseDisabledStyle')) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * Changes user style.
	 * 
	 * @return	array<string>
	 */
	public function changeStyle() {
		StyleHandler::getInstance()->changeStyle($this->style->styleID);
		if (StyleHandler::getInstance()->getStyle()->styleID == $this->style->styleID) {
			WCF::getSession()->setStyleID($this->style->styleID);
		}
	}
	
	/**
	 * Validates the 'getStyleChooser' action.
	 */
	public function validateGetStyleChooser() {
		// does nothing
	}
	
	/**
	 * Returns the style chooser dialog.
	 * 
	 * @return	array<string>
	 */
	public function getStyleChooser() {
		$styleList = new StyleList();
		if (!WCF::getSession()->getPermission('admin.style.canUseDisabledStyle')) {
			$styleList->getConditionBuilder()->add("style.isDisabled = ?", array(0));
		}
		$styleList->sqlOrderBy = "style.styleName ASC";
		$styleList->readObjects();
		
		WCF::getTPL()->assign(array(
			'styleList' => $styleList
		));
		
		return array(
			'actionName' => 'getStyleChooser',
			'template' => WCF::getTPL()->fetch('styleChooser')
		);
	}
}
