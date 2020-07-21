<?php
namespace wcf\data\style;
use wcf\data\TDatabaseObjectToggle;
use wcf\data\user\cover\photo\UserCoverPhoto;
use wcf\data\user\UserAction;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IToggleAction;
use wcf\data\IUploadAction;
use wcf\system\cache\builder\StyleCacheBuilder;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\file\upload\UploadHandler;
use wcf\system\image\ImageHandler;
use wcf\system\language\LanguageFactory;
use wcf\system\request\LinkHandler;
use wcf\system\style\StyleHandler;
use wcf\system\upload\DefaultUploadFileValidationStrategy;
use wcf\system\upload\UploadFile;
use wcf\system\Regex;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\ImageUtil;

/**
 * Executes style-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Style
 * 
 * @method	StyleEditor[]	getObjects()
 * @method	StyleEditor	getSingleObject()
 */
class StyleAction extends AbstractDatabaseObjectAction implements IToggleAction, IUploadAction {
	use TDatabaseObjectToggle;
	
	/**
	 * @inheritDoc
	 */
	protected $allowGuestAccess = ['changeStyle', 'getStyleChooser'];
	
	/**
	 * @inheritDoc
	 */
	protected $className = StyleEditor::class;
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsDelete = ['admin.style.canManageStyle'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsUpdate = ['admin.style.canManageStyle'];
	
	/**
	 * @inheritDoc
	 */
	protected $requireACP = ['copy', 'delete', 'deleteCoverPhoto', 'markAsTainted', 'setAsDefault', 'toggle', 'update', 'upload', 'uploadCoverPhoto', 'uploadLogo', 'uploadLogoMobile'];
	
	/**
	 * style object
	 * @var	Style
	 */
	public $style;
	
	/**
	 * style editor object
	 * @var	StyleEditor
	 */
	public $styleEditor;
	
	/**
	 * @inheritDoc
	 * @return	Style
	 */
	public function create() {
		/** @var Style $style */
		$style = parent::create();
		
		// add variables
		$this->updateVariables($style);
		
		// handle style preview image
		$this->updateStylePreviewImage($style);
		
		return $style;
	}
	
	/**
	 * @inheritDoc
	 */
	public function update() {
		parent::update();
		
		foreach ($this->getObjects() as $style) {
			// update variables
			$this->updateVariables($style->getDecoratedObject(), true);
			
			// handle style preview image
			$this->updateStylePreviewImage($style->getDecoratedObject());
			
			// create favicon data
			$this->updateFavicons($style->getDecoratedObject());
			
			// handle the cover photo
			$this->updateCoverPhoto($style->getDecoratedObject());
			
			// reset stylesheet
			StyleHandler::getInstance()->resetStylesheet($style->getDecoratedObject());
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function delete() {
		$count = parent::delete();
		
		foreach ($this->getObjects() as $style) {
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
		
		foreach (['pageLogo', 'pageLogoMobile'] as $type) {
			if (array_key_exists($type, $this->parameters['uploads'])) {
				/** @var \wcf\system\file\upload\UploadFile $file */
				$file = $this->parameters['uploads'][$type];
				
				if ($file !== null) {
					$fileLocation = $file->getLocation();
					$extension = pathinfo($file->getFilename(), PATHINFO_EXTENSION);
					$newName = $type.'.'.$extension;
					$newLocation = $style->getAssetPath().$newName;
					rename($fileLocation, $newLocation);
					$this->parameters['variables'][$type] = $newName;
					$file->setProcessed($newLocation);
				}
				else {
					$this->parameters['variables'][$type] = '';
				}
			}
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
		foreach (['image', 'image2x'] as $type) {
			if (array_key_exists($type, $this->parameters['uploads'])) {
				/** @var \wcf\system\file\upload\UploadFile $file */
				$file = $this->parameters['uploads'][$type];
				
				if ($style->{$type} && file_exists($style->getAssetPath().basename($style->{$type}))) {
					if (!$file || $style->getAssetPath().basename($style->{$type}) !== $file->getLocation()) {
						unlink($style->getAssetPath().basename($style->{$type}));
					}
				}
				if ($file !== null) {
					$fileLocation = $file->getLocation();
					if (($imageData = getimagesize($fileLocation)) === false) {
						throw new \InvalidArgumentException('The given '.$type.' is not an image');
					}
					$extension = ImageUtil::getExtensionByMimeType($imageData['mime']);
					if ($type === 'image') {
						$newName = 'stylePreview.'.$extension;
					}
					else if ($type === 'image2x') {
						$newName = 'stylePreview@2x.'.$extension;
					}
					else {
						throw new \LogicException('Unreachable');
					}
					$newLocation = $style->getAssetPath().$newName;
					rename($fileLocation, $newLocation);
					(new StyleEditor($style))->update([
						$type => FileUtil::getRelativePath(WCF_DIR.'images/', $style->getAssetPath()).$newName,
					]);
					
					$file->setProcessed($newLocation);
				}
				else {
					(new StyleEditor($style))->update([
						$type => '',
					]);
				}
			}
		}
	}
	
	/**
	 * Updates style favicon files.
	 * 
	 * @param       Style           $style
	 * @since       3.1
	 */
	protected function updateFavicons(Style $style) {
		$styleID = $style->styleID;
		$fileExtension = WCF::getSession()->getVar('styleFavicon-template-'.$styleID);
		$hasFavicon = (bool)$style->hasFavicon;
		if ($fileExtension) {
			$template = WCF_DIR . "images/favicon/{$styleID}.favicon-template.{$fileExtension}";
			$images = [
				'android-chrome-192x192.png' => 192,
				'android-chrome-256x256.png' => 256,
				'apple-touch-icon.png' => 180,
				'mstile-150x150.png' => 150
			];
			
			$adapter = ImageHandler::getInstance()->getAdapter();
			$adapter->loadFile($template);
			foreach ($images as $filename => $length) {
				$thumbnail = $adapter->createThumbnail($length, $length);
				$adapter->writeImage($thumbnail, WCF_DIR."images/favicon/{$styleID}.{$filename}");
			}
			
			// create ico
			require(WCF_DIR . 'lib/system/api/chrisjean/php-ico/class-php-ico.php');
			$phpIco = new \PHP_ICO($template, [
				[16, 16],
				[32, 32]
			]);
			$phpIco->save_ico(WCF_DIR . "images/favicon/{$styleID}.favicon.ico");
			
			$hasFavicon = true;
			
			(new StyleEditor($style))->update(['hasFavicon' => 1]);
			WCF::getSession()->unregister('styleFavicon-template-'.$style->styleID);
		}
		
		if ($hasFavicon) {
			// update manifest.json
			$manifest = <<<MANIFEST
{
    "name": "",
    "icons": [
        {
            "src": "{$styleID}.android-chrome-192x192.png",
            "sizes": "192x192",
            "type": "image/png"
        },
        {
            "src": "{$styleID}.android-chrome-256x256.png",
            "sizes": "256x256",
            "type": "image/png"
        }
    ],
    "theme_color": "#ffffff",
    "background_color": "#ffffff",
    "display": "standalone"
}
MANIFEST;
			file_put_contents(WCF_DIR . "images/favicon/{$styleID}.manifest.json", $manifest);
			
			$style->loadVariables();
			$tileColor = $style->getVariable('wcfHeaderBackground', true);
			
			// update browserconfig.xml
			$browserconfig = <<<BROWSERCONFIG
<?xml version="1.0" encoding="utf-8"?>
<browserconfig>
    <msapplication>
        <tile>
            <square150x150logo src="{$styleID}.mstile-150x150.png"/>
            <TileColor>{$tileColor}</TileColor>
        </tile>
    </msapplication>
</browserconfig>
BROWSERCONFIG;
			file_put_contents(WCF_DIR . "images/favicon/{$styleID}.browserconfig.xml", $browserconfig);
		}
	}
	
	/**
	 * Updates the style cover photo.
	 * 
	 * @param       Style           $style
	 * @since       3.1
	 */
	protected function updateCoverPhoto(Style $style) {
		$styleID = $style->styleID;
		$fileExtension = WCF::getSession()->getVar('styleCoverPhoto-'.$styleID);
		if ($fileExtension) {
			// remove old image
			if ($style->coverPhotoExtension) {
				@unlink(WCF_DIR . 'images/coverPhotos/' . $style->getCoverPhoto());
			}
			
			rename(
				WCF_DIR . 'images/coverPhotos/' . $styleID . '.tmp.' . $fileExtension,
				WCF_DIR . 'images/coverPhotos/' . $styleID . '.' . $fileExtension
			);
			
			(new StyleEditor($style))->update(['coverPhotoExtension' => $fileExtension]);
			WCF::getSession()->unregister('styleCoverPhoto-'.$style->styleID);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function validateUpload() {
		// check upload permissions
		if (!WCF::getSession()->getPermission('admin.style.canManageStyle')) {
			throw new PermissionDeniedException();
		}
		
		$this->readBoolean('is2x', true);
		$this->readString('tmpHash');
		$this->readInteger('styleID', true);
		
		if ($this->parameters['styleID']) {
			$styles = StyleHandler::getInstance()->getStyles();
			if (!isset($styles[$this->parameters['styleID']])) {
				throw new UserInputException('styleID');
			}
			
			$this->style = $styles[$this->parameters['styleID']];
		}
		
		/** @var UploadHandler $uploadHandler */
		$uploadHandler = $this->parameters['__files'];
		
		if (count($uploadHandler->getFiles()) != 1) {
			throw new IllegalLinkException();
		}
		
		// check max filesize, allowed file extensions etc.
		$uploadHandler->validateFiles(new DefaultUploadFileValidationStrategy(PHP_INT_MAX, ['jpg', 'jpeg', 'png', 'gif', 'svg']));
	}
	
	/**
	 * @inheritDoc
	 */
	public function upload() {
		// save files
		/** @noinspection PhpUndefinedMethodInspection */
		/** @var UploadFile[] $files */
		$files = $this->parameters['__files']->getFiles();
		$file = $files[0];
		
		$multiplier = ($this->parameters['is2x']) ? 2 : 1;
		
		try {
			if (!$file->getValidationErrorType()) {
				// shrink preview image if necessary
				$fileLocation = $file->getLocation();
				try {
					if (($imageData = getimagesize($fileLocation)) === false) {
						throw new UserInputException('image');
					}
					switch ($imageData[2]) {
						case IMAGETYPE_PNG:
						case IMAGETYPE_JPEG:
						case IMAGETYPE_GIF:
							// fine
						break;
						default:
							throw new UserInputException('image');
					}
					
					if ($imageData[0] > (Style::PREVIEW_IMAGE_MAX_WIDTH * $multiplier) || $imageData[1] > (Style::PREVIEW_IMAGE_MAX_HEIGHT * $multiplier)) {
						$adapter = ImageHandler::getInstance()->getAdapter();
						$adapter->loadFile($fileLocation);
						$fileLocation = FileUtil::getTemporaryFilename();
						$thumbnail = $adapter->createThumbnail(Style::PREVIEW_IMAGE_MAX_WIDTH * $multiplier, Style::PREVIEW_IMAGE_MAX_HEIGHT * $multiplier, false);
						$adapter->writeImage($thumbnail, $fileLocation);
					}
				}
				catch (SystemException $e) {
					throw new UserInputException('image');
				}
				
				// move uploaded file
				if (@copy($fileLocation, WCF_DIR.'images/stylePreview-'.$this->parameters['tmpHash'].($this->parameters['is2x'] ? '@2x' : '').'.'.$file->getFileExtension())) {
					@unlink($fileLocation);
					
					// store extension within session variables
					WCF::getSession()->register('stylePreview-'.$this->parameters['tmpHash'].($this->parameters['is2x'] ? '@2x' : ''), $file->getFileExtension());
					
					if ($this->parameters['styleID']) {
						$this->updateStylePreviewImage($this->style);
						
						return [
							'url' => WCF::getPath().'images/stylePreview-'.$this->parameters['styleID'].($this->parameters['is2x'] ? '@2x' : '').'.'.$file->getFileExtension()
						];
					}
					
					// return result
					return [
						'url' => WCF::getPath().'images/stylePreview-'.$this->parameters['tmpHash'].($this->parameters['is2x'] ? '@2x' : '').'.'.$file->getFileExtension()
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
		/** @noinspection PhpUndefinedMethodInspection */
		/** @var UploadFile[] $files */
		$files = $this->parameters['__files']->getFiles();
		$file = $files[0];
		
		try {
			$relativePath = FileUtil::unifyDirSeparator(FileUtil::getRelativePath(WCF_DIR.'images/', WCF_DIR.$this->parameters['imagePath']));
			if (strpos($relativePath, '../') !== false) {
				throw new UserInputException('imagePath', 'invalid');
			}
			
			if ($this->parameters['type'] !== 'styleLogo' && $this->parameters['type'] !== 'styleLogo-mobile') {
				throw new UserInputException('type', 'invalid');
			}
			
			if (!$file->getValidationErrorType()) {
				// shrink avatar if necessary
				$fileLocation = $file->getLocation();
				
				$basename = $this->parameters['type'].'-'.$this->parameters['tmpHash'].'.'.$file->getFileExtension();
				$target = WCF_DIR.$this->parameters['imagePath'].'/'.$basename;
				
				// move uploaded file
				if (@copy($fileLocation, $target)) {
					@unlink($fileLocation);
					
					// get logo size
					list($width, $height) = getimagesize($target);
					
					// return result
					return [
						'url' => $basename,
						'width' => $width,
						'height' => $height
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
	 * Validates parameters to upload a favicon.
	 * 
	 * @since       3.1
	 */
	public function validateUploadFavicon() {
		// ignore tmp hash, uploading is supported for existing styles only
		// and files will be finally processed on form submit
		$this->parameters['tmpHash'] = '@@@WCF_INVALID_TMP_HASH@@@';
		
		$this->validateUpload();
	}
	
	/**
	 * Handles favicon upload.
	 *
	 * @return	string[]
	 * @since       3.1
	 */
	public function uploadFavicon() {
		// save files
		/** @noinspection PhpUndefinedMethodInspection */
		/** @var UploadFile[] $files */
		$files = $this->parameters['__files']->getFiles();
		$file = $files[0];
		
		try {
			if (!$file->getValidationErrorType()) {
				$fileLocation = $file->getLocation();
				try {
					if (($imageData = getimagesize($fileLocation)) === false) {
						throw new UserInputException('favicon');
					}
					switch ($imageData[2]) {
						case IMAGETYPE_PNG:
						case IMAGETYPE_JPEG:
						case IMAGETYPE_GIF:
							// fine
							break;
						default:
							throw new UserInputException('favicon');
					}
					
					if ($imageData[0] != Style::FAVICON_IMAGE_WIDTH || $imageData[1] != Style::FAVICON_IMAGE_HEIGHT) {
						throw new UserInputException('favicon', 'dimensions');
					}
				}
				catch (SystemException $e) {
					throw new UserInputException('favicon');
				}
				
				// move uploaded file
				if (@copy($fileLocation, WCF_DIR.'images/favicon/'.$this->style->styleID.'.favicon-template.'.$file->getFileExtension())) {
					@unlink($fileLocation);
					
					// store extension within session variables
					WCF::getSession()->register('styleFavicon-template-'.$this->style->styleID, $file->getFileExtension());
					
					// return result
					return [
						'url' => WCF::getPath().'images/favicon/'.$this->style->styleID.'.favicon-template.'.$file->getFileExtension()
					];
				}
				else {
					throw new UserInputException('favicon', 'uploadFailed');
				}
			}
		}
		catch (UserInputException $e) {
			$file->setValidationErrorType($e->getType());
		}
		
		return ['errorType' => $file->getValidationErrorType()];
	}
	
	/**
	 * Validates parameters to upload a cover photo.
	 *
	 * @since       3.1
	 */
	public function validateUploadCoverPhoto() {
		if (!MODULE_USER_COVER_PHOTO) {
			throw new PermissionDeniedException();
		}
		
		// ignore tmp hash, uploading is supported for existing styles only
		// and files will be finally processed on form submit
		$this->parameters['tmpHash'] = '@@@WCF_INVALID_TMP_HASH@@@';
		
		$this->validateUpload();
	}
	
	/**
	 * Handles the cover photo upload.
	 *
	 * @return	string[]
	 * @since       3.1
	 */
	public function uploadCoverPhoto() {
		// save files
		/** @noinspection PhpUndefinedMethodInspection */
		/** @var UploadFile[] $files */
		$files = $this->parameters['__files']->getFiles();
		$file = $files[0];
		
		try {
			if (!$file->getValidationErrorType()) {
				$fileLocation = $file->getLocation();
				try {
					if (($imageData = getimagesize($fileLocation)) === false) {
						throw new UserInputException('coverPhoto');
					}
					switch ($imageData[2]) {
						case IMAGETYPE_PNG:
						case IMAGETYPE_JPEG:
						case IMAGETYPE_GIF:
							// fine
							break;
						default:
							throw new UserInputException('coverPhoto');
					}
					
					if ($imageData[0] < UserCoverPhoto::MIN_WIDTH) {
						throw new UserInputException('coverPhoto', 'minWidth');
					}
					else if ($imageData[1] < UserCoverPhoto::MIN_HEIGHT) {
						throw new UserInputException('coverPhoto', 'minHeight');
					}
				}
				catch (SystemException $e) {
					throw new UserInputException('coverPhoto');
				}
				
				// move uploaded file
				if (@copy($fileLocation, WCF_DIR.'images/coverPhotos/'.$this->style->styleID.'.tmp.'.$file->getFileExtension())) {
					@unlink($fileLocation);
					
					// store extension within session variables
					WCF::getSession()->register('styleCoverPhoto-'.$this->style->styleID, $file->getFileExtension());
					
					// return result
					return [
						'url' => WCF::getPath().'images/coverPhotos/'.$this->style->styleID.'.tmp.'.$file->getFileExtension()
					];
				}
				else {
					throw new UserInputException('coverPhoto', 'uploadFailed');
				}
			}
		}
		catch (UserInputException $e) {
			$file->setValidationErrorType($e->getType());
		}
		
		return ['errorType' => $file->getValidationErrorType()];
	}
	
	/**
	 * Validates the parameters to delete a style's default cover photo.
	 * 
	 * @throws      PermissionDeniedException
	 * @throws      UserInputException
	 * @since       3.1
	 */
	public function validateDeleteCoverPhoto() {
		if (!MODULE_USER_COVER_PHOTO) {
			throw new PermissionDeniedException();
		}
		
		$this->styleEditor = $this->getSingleObject();
		if (!$this->styleEditor->coverPhotoExtension) {
			throw new UserInputException('objectIDs');
		}
	}
	
	/**
	 * Deletes a style's default cover photo.
	 * 
	 * @return      string[]
	 * @since       3.1
	 */
	public function deleteCoverPhoto() {
		$this->styleEditor->deleteCoverPhoto();
		
		return [
			'url' => WCF::getPath().'images/coverPhotos/'.(new Style($this->styleEditor->styleID))->getCoverPhoto()
		];
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
		
		$number = count($numbers) ? max($numbers) + 1 : 2;
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
			'imagePath' => $this->styleEditor->imagePath,
			'apiVersion' => $this->styleEditor->apiVersion
		]);
		
		// check if style description uses i18n
		if (preg_match('~^wcf.style.styleDescription\d+$~', $newStyle->styleDescription)) {
			$styleDescription = 'wcf.style.styleDescription'.$newStyle->styleID;
			
			// delete any phrases that were the result of an import
			$sql = "DELETE FROM     wcf".WCF_N."_language_item
				WHERE           languageItem = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$styleDescription]);
			
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
			
			LanguageFactory::getInstance()->deleteLanguageCache();
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
		foreach (['image', 'image2x'] as $imageType) {
			$image = $this->styleEditor->{$imageType};
			if ($image) {
				// get extension
				$fileExtension = mb_substr($image, mb_strrpos($image, '.'));
				
				// copy existing preview image
				if (@copy(WCF_DIR . 'images/' . $image, WCF_DIR . 'images/stylePreview-' . $newStyle->styleID . $fileExtension)) {
					// bypass StyleEditor::update() to avoid scaling of already fitting image
					$sql = "UPDATE	wcf" . WCF_N . "_style
						SET	".$imageType." = ?
						WHERE	styleID = ?";
					$statement = WCF::getDB()->prepareStatement($sql);
					$statement->execute([
						'stylePreview-' . $newStyle->styleID . $fileExtension,
						$newStyle->styleID
					]);
				}
			}
		}
		
		// copy cover photo
		if ($this->styleEditor->coverPhotoExtension) {
			if (@copy(WCF_DIR . "images/coverPhotos/{$this->styleEditor->styleID}.{$this->styleEditor->coverPhotoExtension}", WCF_DIR . "images/coverPhotos/{$newStyle->styleID}.{$this->styleEditor->coverPhotoExtension}")) {
				$sql = "UPDATE	wcf" . WCF_N . "_style
					SET	coverPhotoExtension = ?
					WHERE	styleID = ?";
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute([
					$this->styleEditor->coverPhotoExtension,
					$newStyle->styleID
				]);
			}
		}
		
		// copy favicon
		if ($this->styleEditor->hasFavicon) {
			$path = WCF_DIR . 'images/favicon/';
			foreach (glob($path . "{$this->styleEditor->styleID}.*") as $filepath) {
				@copy($filepath, $path . preg_replace('~^\d+\.~', "{$newStyle->styleID}.", basename($filepath)));
			}
			
			$sql = "UPDATE	wcf" . WCF_N . "_style
				SET	hasFavicon = ?
				WHERE	styleID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([
				1,
				$newStyle->styleID
			]);
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
	 * @inheritDoc
	 */
	public function validateToggle() {
		parent::validateUpdate();
		
		foreach ($this->getObjects() as $style) {
			if ($style->isDefault) {
				throw new UserInputException('objectIDs');
			}
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
	 */
	public function changeStyle() {
		StyleHandler::getInstance()->changeStyle($this->style->styleID);
		if (StyleHandler::getInstance()->getStyle()->styleID == $this->style->styleID) {
			WCF::getSession()->setStyleID($this->style->styleID);
			
			if (WCF::getUser()->userID) {
				// set this as the permanent style
				$userAction = new UserAction([WCF::getUser()], 'update', ['data' => [
					'styleID' => $this->style->isDefault ? 0 : $this->style->styleID
				]]);
				$userAction->executeAction();
			}
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
	 * Validates the mark as tainted action.
	 * 
	 * @since	3.0
	 */
	public function validateMarkAsTainted() {
		if (!WCF::getSession()->getPermission('admin.style.canManageStyle')) {
			throw new PermissionDeniedException();
		}
		
		$this->styleEditor = $this->getSingleObject();
	}
	
	/**
	 * Marks a style as tainted.
	 * 
	 * @since	3.0
	 */
	public function markAsTainted() {
		// merge definitions
		$variables = $this->styleEditor->getVariables();
		$variables['individualScss'] = str_replace("/* WCF_STYLE_CUSTOM_USER_MODIFICATIONS */\n", '', $variables['individualScss']);
		$variables['overrideScss'] = str_replace("/* WCF_STYLE_CUSTOM_USER_MODIFICATIONS */\n", '', $variables['overrideScss']);
		$this->styleEditor->setVariables($variables);
		
		$this->styleEditor->update([
			'isTainted' => 1,
			'packageName' => ''
		]);
	}
}
