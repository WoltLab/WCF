<?php
namespace wcf\data\style;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IToggleAction;
use wcf\data\IUploadAction;
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
class StyleAction extends AbstractDatabaseObjectAction implements IToggleAction, IUploadAction {
	/**
	 * @inheritdoc
	 */
	protected $allowGuestAccess = ['changeStyle', 'getStyleChooser'];
	
	/**
	 * @inheritdoc
	 */
	protected $className = 'wcf\data\style\StyleEditor';
	
	/**
	 * @inheritdoc
	 */
	protected $permissionsDelete = ['admin.style.canManageStyle'];
	
	/**
	 * @inheritdoc
	 */
	protected $permissionsUpdate = ['admin.style.canManageStyle'];
	
	/**
	 * @inheritdoc
	 */
	protected $requireACP = ['copy', 'delete', 'markAsTainted', 'setAsDefault', 'toggle', 'update', 'upload', 'uploadLogo'];
	
	/**
	 * style object
	 * @var	Style
	 */
	public $style = null;
	
	/**
	 * style editor object
	 * @var	StyleEditor
	 */
	public $styleEditor = null;
	
	/**
	 * @inheritdoc
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
	 * @inheritdoc
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
	 * @inheritdoc
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
	 * @param	Style		$style
	 * @param	boolean		$removePreviousVariables
	 */
	protected function updateVariables(Style $style, $removePreviousVariables = false) {
		if (!isset($this->parameters['variables']) || !is_array($this->parameters['variables'])) {
			return;
		}
		
		$sql = "SELECT	variableID, variableName, defaultValue
			FROM	wcf".WCF_N."_style_variable";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		$variables = [];
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
			$statement->execute([$style->styleID]);
		}
		
		// insert variables that differ from default values
		if (!empty($variables)) {
			$sql = "INSERT INTO	wcf".WCF_N."_style_variable_value
						(styleID, variableID, variableValue)
				VALUES		(?, ?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			WCF::getDB()->beginTransaction();
			foreach ($variables as $variableID => $variableValue) {
				$statement->execute([
					$style->styleID,
					$variableID,
					$variableValue
				]);
			}
			WCF::getDB()->commitTransaction();
		}
	}
	
	/**
	 * Updates style preview image.
	 * 
	 * @param	Style		$style
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
						$statement->execute([
							$filename,
							$style->styleID
						]);
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
	 * @inheritdoc
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
		$this->parameters['__files']->validateFiles(new DefaultUploadFileValidationStrategy(PHP_INT_MAX, ['jpg', 'jpeg', 'png', 'gif']));
	}
	
	/**
	 * @inheritdoc
	 */
	public function upload() {
		// save files
		$files = $this->parameters['__files']->getFiles();
		$file = $files[0];
		
		try {
			if (!$file->getValidationErrorType()) {
				// shrink preview image if necessary
				$fileLocation = $file->getLocation();
				try {
					if (($imageData = getimagesize($fileLocation)) === false) {
						throw new UserInputException('image');
					}
					switch ($imageData[2]) {
						case IMG_PNG:
						case IMG_JPEG:
						case IMG_JPG:
						case IMG_GIF:
							// fine
						break;
						default:
							throw new UserInputException('image');
					}

					if ($imageData[0] > Style::PREVIEW_IMAGE_MAX_WIDTH || $imageData[1] > Style::PREVIEW_IMAGE_MAX_HEIGHT) {
						$adapter = ImageHandler::getInstance()->getAdapter();
						$adapter->loadFile($fileLocation);
						$fileLocation = FileUtil::getTemporaryFilename();
						$thumbnail = $adapter->createThumbnail(Style::PREVIEW_IMAGE_MAX_WIDTH, Style::PREVIEW_IMAGE_MAX_HEIGHT, false);
						$adapter->writeImage($thumbnail, $fileLocation);
						$imageData = getimagesize($fileLocation);
					}
				}
				catch (SystemException $e) {
					throw new UserInputException('image');
				}
				
				// move uploaded file
				if (@copy($fileLocation, WCF_DIR.'images/stylePreview-'.$this->parameters['tmpHash'].'.'.$file->getFileExtension())) {
					@unlink($fileLocation);
					
					// store extension within session variables
					WCF::getSession()->register('stylePreview-'.$this->parameters['tmpHash'], $file->getFileExtension());
					
					if ($this->parameters['styleID']) {
						$this->updateStylePreviewImage($this->style);
						
						return [
							'url' => WCF::getPath().'images/stylePreview-'.$this->parameters['styleID'].'.'.$file->getFileExtension()
						];
					}
					
					// return result
					return [
						'url' => WCF::getPath().'images/stylePreview-'.$this->parameters['tmpHash'].'.'.$file->getFileExtension()
					];
				}
				else {
					throw new UserInputException('image', 'uploadFailed');
				}
			}
		}
		catch (UserInputException $e) {
			$file->setValidationErrorType($e->getType());
		}
		
		return ['errorType' => $file->getValidationErrorType()];
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
	 * @return	string[]
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
					return [
						'url' => WCF::getPath().'images/styleLogo-'.$this->parameters['tmpHash'].'.'.$file->getFileExtension()
					];
				}
				else {
					throw new UserInputException('image', 'uploadFailed');
				}
			}
		}
		catch (UserInputException $e) {
			$file->setValidationErrorType($e->getType());
		}
		
		return ['errorType' => $file->getValidationErrorType()];
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
	 * @return	string[]
	 */
	public function copy() {
		// get unique style name
		$sql = "SELECT	styleName
			FROM	wcf".WCF_N."_style
			WHERE	styleName LIKE ?
				AND styleID <> ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			$this->styleEditor->styleName.'%',
			$this->styleEditor->styleID
		]);
		$numbers = [];
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
		$newStyle = StyleEditor::create([
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
		]);
		
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
			$statement->execute([$newStyle->styleDescription]);
			
			// update style description
			$styleEditor = new StyleEditor($newStyle);
			$styleEditor->update([
				'styleDescription' => $styleDescription
			]);
		}
		
		// copy style variables
		$sql = "INSERT INTO	wcf".WCF_N."_style_variable_value
					(styleID, variableID, variableValue)
			SELECT		".$newStyle->styleID." AS styleID, value.variableID, value.variableValue
			FROM		wcf".WCF_N."_style_variable_value value
			WHERE		value.styleID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$this->styleEditor->styleID]);
		
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
				$statement->execute([
					'stylePreview-'.$newStyle->styleID.$fileExtension,
					$newStyle->styleID
				]);
			}
		}
		
		// copy images
		if ($this->styleEditor->imagePath && is_dir(WCF_DIR . $this->styleEditor->imagePath)) {
			$path = FileUtil::removeTrailingSlash($this->styleEditor->imagePath);
			$newPath = '';
			$i = 2;
			while (true) {
				$newPath = "{$path}-{$i}/";
				if (!file_exists(WCF_DIR . $newPath)) {
					break;
				}
				
				$i++;
			}
			
			if (!FileUtil::makePath(WCF_DIR . $newPath)) {
				$newPath = '';
			}
			
			if ($newPath) {
				$src = FileUtil::addTrailingSlash(WCF_DIR . $this->styleEditor->imagePath);
				$dst = WCF_DIR . $newPath;
				
				$dir = opendir($src);
				while (($file = readdir($dir)) !== false) {
					if ($file != '.' && $file != '..' && !is_dir($file)) {
						@copy($src . $file, $dst . $file);
					}
				}
				closedir($dir);
			}
			
			$sql = "UPDATE	wcf".WCF_N."_style
				SET	imagePath = ?
				WHERE	styleID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([
				$newPath,
				$newStyle->styleID
			]);
		}
		
		StyleCacheBuilder::getInstance()->reset();
		
		return [
			'redirectURL' => LinkHandler::getInstance()->getLink('StyleEdit', ['id' => $newStyle->styleID])
		];
	}
	
	/**
	 * @inheritdoc
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
	 * @inheritdoc
	 */
	public function toggle() {
		foreach ($this->objects as $style) {
			$isDisabled = ($style->isDisabled) ? 0 : 1;
			$style->update(['isDisabled' => $isDisabled]);
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
	 * @return	string[]
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
	 * @return	string[]
	 */
	public function getStyleChooser() {
		$styleList = new StyleList();
		if (!WCF::getSession()->getPermission('admin.style.canUseDisabledStyle')) {
			$styleList->getConditionBuilder()->add("style.isDisabled = ?", [0]);
		}
		$styleList->sqlOrderBy = "style.styleName ASC";
		$styleList->readObjects();
		
		WCF::getTPL()->assign([
			'styleList' => $styleList
		]);
		
		return [
			'actionName' => 'getStyleChooser',
			'template' => WCF::getTPL()->fetch('styleChooser')
		];
	}
	
	/**
	 * TODO: add documentation
	 * @since	2.2
	 */
	public function validateMarkAsTainted() {
		if (!WCF::getSession()->getPermission('admin.style.canManageStyle')) {
			throw new PermissionDeniedException();
		}
		
		$this->styleEditor = $this->getSingleObject();
	}
	
	/**
	 * TODO: add documentation
	 * @since	2.2
	 */
	public function markAsTainted() {
		// merge definitions
		$variables = $this->styleEditor->getVariables();
		$variables['individualScss'] = str_replace("/* WCF_STYLE_CUSTOM_USER_MODIFICATIONS */\n", '', $variables['individualScss']);
		$variables['overrideScss'] = str_replace("/* WCF_STYLE_CUSTOM_USER_MODIFICATIONS */\n", '', $variables['overrideScss']);
		$this->styleEditor->setVariables($variables);
		
		$this->styleEditor->update([
			'isTainted' => 1
		]);
	}
}
