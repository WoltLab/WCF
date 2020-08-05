<?php
namespace wcf\data\style;
use wcf\data\TDatabaseObjectToggle;
use wcf\data\user\UserAction;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IToggleAction;
use wcf\system\cache\builder\StyleCacheBuilder;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\image\ImageHandler;
use wcf\system\language\LanguageFactory;
use wcf\system\request\LinkHandler;
use wcf\system\style\StyleHandler;
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
class StyleAction extends AbstractDatabaseObjectAction implements IToggleAction {
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
	protected $requireACP = ['copy', 'delete', 'markAsTainted', 'setAsDefault', 'toggle', 'update', 'upload',];
	
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
		
		// create favicon data
		$this->updateFavicons($style);
		
		// handle the cover photo
		$this->updateCoverPhoto($style);
			
		// handle custom assets
		$this->updateCustomAssets($style);
		
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
			
			// handle custom assets
			$this->updateCustomAssets($style->getDecoratedObject());
			
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
		
		$style->loadVariables();
		foreach (['pageLogo', 'pageLogoMobile'] as $type) {
			if (array_key_exists($type, $this->parameters['uploads'])) {
				/** @var \wcf\system\file\upload\UploadFile $file */
				$file = $this->parameters['uploads'][$type];
				
				if ($style->getVariable($type) && file_exists($style->getAssetPath().basename($style->getVariable($type)))) {
					if (!$file || $style->getAssetPath().basename($style->getVariable($type)) !== $file->getLocation()) {
						unlink($style->getAssetPath().basename($style->getVariable($type)));
					}
				}
				
				if ($file !== null) {
					$fileLocation = $file->getLocation();
					$extension = pathinfo($file->getFilename(), PATHINFO_EXTENSION);
					$newName = $type.'-'.\bin2hex(\random_bytes(4)).'.'.$extension;
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
		$images = [
			'android-chrome-192x192.png' => 192,
			'android-chrome-256x256.png' => 256,
			'apple-touch-icon.png' => 180,
			'mstile-150x150.png' => 150
		];
		
		$hasFavicon = $style->hasFavicon;
		if (array_key_exists('favicon', $this->parameters['uploads'])) {
			/** @var \wcf\system\file\upload\UploadFile $file */
			$file = $this->parameters['uploads']['favicon'];
			
			if ($file !== null) {
				$fileLocation = $file->getLocation();
				if (($imageData = getimagesize($fileLocation)) === false) {
					throw new \InvalidArgumentException('The given favicon is not an image');
				}
				$extension = ImageUtil::getExtensionByMimeType($imageData['mime']);
				$newName = "favicon.template.".$extension;
				$newLocation = $style->getAssetPath().$newName;
				rename($fileLocation, $newLocation);
				
				// Create browser specific files.
				$adapter = ImageHandler::getInstance()->getAdapter();
				$adapter->loadFile($newLocation);
				foreach ($images as $filename => $length) {
					$thumbnail = $adapter->createThumbnail($length, $length);
					$adapter->writeImage($thumbnail, $style->getAssetPath().$filename);
				}
				
				// Create ICO file.
				require(WCF_DIR . 'lib/system/api/chrisjean/php-ico/class-php-ico.php');
				(new \PHP_ICO($newLocation, [
					[16, 16],
					[32, 32]
				]))->save_ico($style->getAssetPath()."favicon.ico");
				
				(new StyleEditor($style))->update([
					'hasFavicon' => 1,
				]);
				
				$file->setProcessed($newLocation);
				$hasFavicon = true;
			}
			else {
				foreach ($images as $filename => $length) {
					unlink($style->getAssetPath().$filename);
				}
				unlink($style->getAssetPath()."favicon.ico");
				foreach (['png', 'jpg', 'gif'] as $extension) {
					if (file_exists($style->getAssetPath()."favicon.template.".$extension)) {
						unlink($style->getAssetPath()."favicon.template.".$extension);
					}
				}
				(new StyleEditor($style))->update([
					'hasFavicon' => 0,
				]);
				
				$hasFavicon = false;
			}
		}
		
		if ($hasFavicon) {
			// update manifest.json
			$manifest = <<<MANIFEST
{
    "name": "",
    "icons": [
        {
            "src": "android-chrome-192x192.png",
            "sizes": "192x192",
            "type": "image/png"
        },
        {
            "src": "android-chrome-256x256.png",
            "sizes": "256x256",
            "type": "image/png"
        }
    ],
    "theme_color": "#ffffff",
    "background_color": "#ffffff",
    "display": "standalone"
}
MANIFEST;
			file_put_contents($style->getAssetPath()."manifest.json", $manifest);
			
			$style->loadVariables();
			$tileColor = $style->getVariable('wcfHeaderBackground', true);
			
			// update browserconfig.xml
			$browserconfig = <<<BROWSERCONFIG
<?xml version="1.0" encoding="utf-8"?>
<browserconfig>
    <msapplication>
        <tile>
            <square150x150logo src="mstile-150x150.png"/>
            <TileColor>{$tileColor}</TileColor>
        </tile>
    </msapplication>
</browserconfig>
BROWSERCONFIG;
			file_put_contents($style->getAssetPath()."browserconfig.xml", $browserconfig);
		}
	}
	
	/**
	 * Updates the style cover photo.
	 * 
	 * @param       Style           $style
	 * @since       3.1
	 */
	protected function updateCoverPhoto(Style $style) {
		if (array_key_exists('coverPhoto', $this->parameters['uploads'])) {
			/** @var \wcf\system\file\upload\UploadFile $file */
			$file = $this->parameters['uploads']['coverPhoto'];
			
			if ($style->coverPhotoExtension && file_exists($style->getCoverPhotoLocation())) {
				if (!$file || $style->getCoverPhotoLocation() !== $file->getLocation()) {
					unlink($style->getCoverPhotoLocation());
				}
			}
			if ($file !== null) {
				$fileLocation = $file->getLocation();
				if (($imageData = getimagesize($fileLocation)) === false) {
					throw new \InvalidArgumentException('The given coverPhoto is not an image');
				}
				$extension = ImageUtil::getExtensionByMimeType($imageData['mime']);
				$newLocation = $style->getAssetPath().'coverPhoto.'.$extension;
				rename($fileLocation, $newLocation);
				(new StyleEditor($style))->update([
					'coverPhotoExtension' => $extension,
				]);
				
				$file->setProcessed($newLocation);
			}
			else {
				(new StyleEditor($style))->update([
					'coverPhotoExtension' => '',
				]);
			}
		}
	}
	
	/**
	 * @since       5.2
	 */
	protected function updateCustomAssets(Style $style) {
		$customAssetPath = $style->getAssetPath().'custom/';
		
		if (!empty($this->parameters['customAssets']['removed'])) {
			/** @var \wcf\system\file\upload\UploadFile $file */
			foreach ($this->parameters['customAssets']['removed'] as $file) {
				unlink($file->getLocation());
			}
		}
		if (!empty($this->parameters['customAssets']['added'])) {
			if (!is_dir($customAssetPath)) {
				FileUtil::makePath($customAssetPath);
			}
			
			/** @var \wcf\system\file\upload\UploadFile $file */
			foreach ($this->parameters['customAssets']['added'] as $file) {
				rename($file->getLocation(), $customAssetPath.$file->getFilename());
				$file->setProcessed($customAssetPath.$file->getFilename());
			}
		}
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
			'apiVersion' => $this->styleEditor->apiVersion,
			
			'coverPhotoExtension' => $this->styleEditor->coverPhotoExtension,
			'hasFavicon' => $this->styleEditor->hasFavicon,
		]);
		$styleEditor = new StyleEditor($newStyle);
		
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
				$styleEditor->update([
					$imageType => preg_replace('/^style-\d+/', 'style-'.$styleEditor->styleID, $image),
				]);
			}
		}
		
		// Copy asset path
		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator(
				$this->styleEditor->getAssetPath(),
				\FilesystemIterator::SKIP_DOTS
			), 
			\RecursiveIteratorIterator::SELF_FIRST
		);
		foreach ($iterator as $file) {
			/** @var \SplFileInfo $file */
			if ($file->isDir()) {
				$relativePath = FileUtil::getRelativePath($this->styleEditor->getAssetPath(), $file->getPathname());
			}
			else if ($file->isFile()) {
				$relativePath = FileUtil::getRelativePath($this->styleEditor->getAssetPath(), $file->getPath());
			}
			else {
				throw new \LogicException('Unreachable');
			}
			$targetFolder = $newStyle->getAssetPath().$relativePath;
			FileUtil::makePath($targetFolder);
			if ($file->isFile()) {
				copy($file->getPathname(), $targetFolder.$file->getFilename());
			}
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
