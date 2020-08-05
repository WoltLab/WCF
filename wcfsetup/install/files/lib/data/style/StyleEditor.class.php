<?php
namespace wcf\data\style;
use wcf\data\language\category\LanguageCategory;
use wcf\data\language\LanguageList;
use wcf\data\package\Package;
use wcf\data\package\PackageCache;
use wcf\data\template\group\TemplateGroup;
use wcf\data\template\group\TemplateGroupAction;
use wcf\data\template\Template;
use wcf\data\template\TemplateEditor;
use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\application\ApplicationHandler;
use wcf\system\cache\builder\StyleCacheBuilder;
use wcf\system\exception\SystemException;
use wcf\system\io\Tar;
use wcf\system\io\TarWriter;
use wcf\system\language\LanguageFactory;
use wcf\system\package\PackageArchive;
use wcf\system\style\StyleCompiler;
use wcf\system\style\StyleHandler;
use wcf\system\Regex;
use wcf\system\style\exception\FontDownloadFailed;
use wcf\system\style\FontManager;
use wcf\system\WCF;
use wcf\util\DateUtil;
use wcf\util\FileUtil;
use wcf\util\StringUtil;
use wcf\util\XML;
use wcf\util\XMLWriter;

/**
 * Provides functions to edit, import, export and delete a style.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Style
 * 
 * @method	Style	getDecoratedObject()
 * @mixin	Style
 */
class StyleEditor extends DatabaseObjectEditor implements IEditableCachedObject {
	const EXCLUDE_WCF_VERSION = '6.0.0 Alpha 1';
	const INFO_FILE = 'style.xml';
	const VALID_IMAGE_EXTENSIONS = ['gif', 'jpg', 'jpeg', 'png', 'svg', 'xml', 'json'];
	
	/**
	 * list of compatible API versions
	 * @var integer[]
	 * @deprecated 5.2
	 */
	public static $compatibilityApiVersions = [2018];
	
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Style::class;
	
	/**
	 * @inheritDoc
	 */
	public function update(array $parameters = []) {
		$variables = null;
		if (isset($parameters['variables'])) {
			$variables = $parameters['variables'];
			unset($parameters['variables']);
		}
		
		// update style data
		parent::update($parameters);
		
		// update variables
		if ($variables !== null) {
			$this->setVariables($variables);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function delete() {
		parent::delete();
		
		// delete style files
		$files = @glob(WCF_DIR.'style/style-'.$this->styleID.'*.css');
		if (is_array($files)) {
			foreach ($files as $file) {
				@unlink($file);
			}
		}
		
		// delete preview image
		if ($this->image) {
			@unlink(WCF_DIR.'images/'.$this->image);
		}
		
		// delete language items
		$sql = "DELETE FROM	wcf".WCF_N."_language_item
			WHERE		languageItem = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(['wcf.style.styleDescription'.$this->styleID]);
	}
	
	/**
	 * Sets this style as default style.
	 */
	public function setAsDefault() {
		// remove old default
		$sql = "UPDATE	wcf".WCF_N."_style
			SET	isDefault = ?
			WHERE	isDefault = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([0, 1]);
		
		// set new default
		$this->update([
			'isDefault' => 1,
			'isDisabled' => 0
		]);
		
		self::resetCache();
	}
	
	/**
	 * Deletes the style's default cover photo.
	 */
	public function deleteCoverPhoto() {
		if ($this->coverPhotoExtension) {
			@unlink(WCF_DIR.'images/coverPhotos/'.$this->styleID.'.'.$this->coverPhotoExtension);
			
			$this->update([
				'coverPhotoExtension' => ''
			]);
		}
	}
	
	/**
	 * Returns the list of variables that exist, but have no explicit values for this style.
	 * 
	 * @return      string[]
	 */
	public function getImplicitVariables() {
		$sql = "SELECT          variable.variableName
			FROM            wcf".WCF_N."_style_variable variable
			LEFT JOIN       wcf".WCF_N."_style_variable_value variable_value
			ON              (variable_value.variableID = variable.variableID AND variable_value.styleID = ?)
			WHERE           variable.variableName LIKE ?
					AND variable_value.variableValue IS NULL";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			$this->styleID,
			'wcf%'
		]);
		$variableNames = [];
		while ($variableName = $statement->fetchColumn()) {
			$variableNames[] = $variableName;
		}
		
		return $variableNames;
	}
	
	/**
	 * Reads the data of a style exchange format file.
	 * 
	 * @param	Tar	$tar
	 * @return	array
	 * @throws	SystemException
	 */
	public static function readStyleData(Tar $tar) {
		// search style.xml
		$index = $tar->getIndexByFilename(self::INFO_FILE);
		if ($index === false) {
			throw new SystemException("unable to find required file '".self::INFO_FILE."' in style archive");
		}
		
		// open style.xml
		$xml = new XML();
		$xml->loadXML(self::INFO_FILE, $tar->extractToString($index));
		$xpath = $xml->xpath();
		
		$data = [
			'name' => '', 'description' => [], 'version' => '', 'image' => '', 'image2x' => '', 'copyright' => '', 'default' => false,
			'license' => '', 'authorName' => '', 'authorURL' => '', 'templates' => '', 'images' => '', 'coverPhoto' => '',
			'variables' => '', 'date' => '0000-00-00', 'imagesPath' => '', 'packageName' => '', 'apiVersion' => '3.0'
		];
		
		$categories = $xpath->query('/ns:style/*');
		foreach ($categories as $category) {
			switch ($category->tagName) {
				case 'author':
					$elements = $xpath->query('child::*', $category);
					foreach ($elements as $element) {
						switch ($element->tagName) {
							case 'authorname':
								$data['authorName'] = $element->nodeValue;
							break;
							
							case 'authorurl':
								$data['authorURL'] = $element->nodeValue;
							break;
						}
					}
				break;
				
				case 'files':
					$elements = $xpath->query('child::*', $category);
					
					/** @var \DOMElement $element */
					foreach ($elements as $element) {
						$data[$element->tagName] = $element->nodeValue;
						if ($element->hasAttribute('path')) {
							$data[$element->tagName.'Path'] = $element->getAttribute('path');
						}
					}
				break;
				
				case 'general':
					$elements = $xpath->query('child::*', $category);
					
					/** @var \DOMElement $element */
					foreach ($elements as $element) {
						switch ($element->tagName) {
							case 'date':
								DateUtil::validateDate($element->nodeValue);
								
								$data['date'] = $element->nodeValue;
							break;
							
							case 'default':
								$data['default'] = true;
							break;
							
							case 'description':
								if ($element->hasAttribute('language')) {
									$data['description'][$element->getAttribute('language')] = $element->nodeValue;
								}
							break;
							
							case 'stylename':
								$data['name'] = $element->nodeValue;
							break;
							
							case 'packageName':
								$data['packageName'] = $element->nodeValue;
							break;
							
							case 'version':
								if (!Package::isValidVersion($element->nodeValue)) {
									throw new SystemException("style version '".$element->nodeValue."' is invalid");
								}
								
								$data['version'] = $element->nodeValue;
							break;
							
							case 'copyright':
							case 'image':
							case 'image2x':
							case 'license':
							case 'coverPhoto':
								$data[$element->tagName] = $element->nodeValue;
							break;
							
							case 'apiVersion':
								if (!in_array($element->nodeValue, Style::$supportedApiVersions)) {
									throw new SystemException("Unknown api version '".$element->nodeValue."'");
								}
								
								$data['apiVersion'] = $element->nodeValue;
								break;
						}
					}
				break;
			}
		}
		
		if (empty($data['name'])) {
			throw new SystemException("required tag 'stylename' is missing in '".self::INFO_FILE."'");
		}
		if (empty($data['variables'])) {
			throw new SystemException("required tag 'variables' is missing in '".self::INFO_FILE."'");
		}
		
		// search variables.xml
		$index = $tar->getIndexByFilename($data['variables']);
		if ($index === false) {
			throw new SystemException("unable to find required file '".$data['variables']."' in style archive");
		}
		
		// open variables.xml
		$data['variables'] = self::readVariablesData($data['variables'], $tar->extractToString($index));
		
		return $data;
	}
	
	/**
	 * Reads the data of a variables.xml file.
	 * 
	 * @param	string		$filename
	 * @param	string		$content
	 * @return	array
	 */
	public static function readVariablesData($filename, $content) {
		// open variables.xml
		$xml = new XML();
		$xml->loadXML($filename, $content);
		$variables = $xml->xpath()->query('/ns:variables/ns:variable');
		
		$data = [];
		
		/** @var \DOMElement $variable */
		foreach ($variables as $variable) {
			$data[$variable->getAttribute('name')] = $variable->nodeValue;
		}
		
		return $data;
	}
	
	/**
	 * Returns the data of a style exchange format file.
	 * 
	 * @param	string		$filename
	 * @return	array
	 */
	public static function getStyleData($filename) {
		// open file
		$tar = new Tar($filename);
		
		// get style data
		$data = self::readStyleData($tar);
		
		// export preview image to temporary location
		if (!empty($data['image'])) {
			$i = $tar->getIndexByFilename($data['image']);
			if ($i !== false) {
				$path = FileUtil::getTemporaryFilename('stylePreview_', $data['image'], WCF_DIR.'tmp/');
				$data['image'] = basename($path);
				$tar->extract($i, $path);
			}
		}
		
		$tar->close();
		
		return $data;
	}
	
	/**
	 * Imports a style.
	 * 
	 * @param	string		$filename
	 * @param	integer		$packageID
	 * @param	StyleEditor	$style
	 * @param	boolean		$skipFontDownload
	 * @return	StyleEditor
	 */
	public static function import($filename, $packageID = 1, StyleEditor $style = null, $skipFontDownload = false) {
		// open file
		$tar = new Tar($filename);
		
		// get style data
		$data = self::readStyleData($tar);
		
		$styleData = [
			'styleName' => $data['name'],
			'variables' => $data['variables'],
			'styleVersion' => $data['version'],
			'styleDate' => $data['date'],
			'copyright' => $data['copyright'],
			'license' => $data['license'],
			'authorName' => $data['authorName'],
			'authorURL' => $data['authorURL'],
			'packageName' => $data['packageName'],
			'apiVersion' => $data['apiVersion']
		];
		
		// check if there is an untainted style with the same package name
		if ($style === null && !empty($styleData['packageName'])) {
			$style = StyleHandler::getInstance()->getStyleByName($styleData['packageName'], true);
		}
		
		// handle templates
		if (!empty($data['templates'])) {
			$templateGroupFolderName = '';
			if ($style !== null && $style->templateGroupID) {
				$templateGroupFolderName = (new TemplateGroup($style->templateGroupID))->templateGroupFolderName;
				$styleData['templateGroupID'] = $style->templateGroupID;
			}
			
			if (empty($templateGroupFolderName)) {
				// create template group
				$templateGroupName = $originalTemplateGroupName = $data['name'];
				$templateGroupFolderName = preg_replace('/[^a-z0-9_-]/i', '', $templateGroupName);
				if (empty($templateGroupFolderName)) $templateGroupFolderName = 'generic'.mb_substr(StringUtil::getRandomID(), 0, 8);
				$originalTemplateGroupFolderName = $templateGroupFolderName;
				
				// get unique template group name
				$i = 1;
				while (true) {
					$sql = "SELECT	COUNT(*)
						FROM	wcf".WCF_N."_template_group
						WHERE	templateGroupName = ?";
					$statement = WCF::getDB()->prepareStatement($sql);
					$statement->execute([$templateGroupName]);
					if (!$statement->fetchSingleColumn()) break;
					$templateGroupName = $originalTemplateGroupName . '_' . $i;
					$i++;
				}
				
				// get unique folder name
				$i = 1;
				while (true) {
					$sql = "SELECT	COUNT(*)
						FROM	wcf".WCF_N."_template_group
						WHERE	templateGroupFolderName = ?";
					$statement = WCF::getDB()->prepareStatement($sql);
					$statement->execute([
						FileUtil::addTrailingSlash($templateGroupFolderName)
					]);
					if (!$statement->fetchSingleColumn()) break;
					$templateGroupFolderName = $originalTemplateGroupFolderName . '_' . $i;
					$i++;
				}
				
				$templateGroupAction = new TemplateGroupAction([], 'create', [
					'data' => [
						'templateGroupName' => $templateGroupName,
						'templateGroupFolderName' => FileUtil::addTrailingSlash($templateGroupFolderName)
					]
				]);
				$returnValues = $templateGroupAction->executeAction();
				$styleData['templateGroupID'] = $returnValues['returnValues']->templateGroupID;
			}
			
			// import templates
			$index = $tar->getIndexByFilename($data['templates']);
			if ($index !== false) {
				// extract templates tar
				$destination = FileUtil::getTemporaryFilename('templates_');
				$tar->extract($index, $destination);
				
				// open templates tar and group templates by package
				$templatesTar = new Tar($destination);
				$contentList = $templatesTar->getContentList();
				$packageToTemplates = [];
				foreach ($contentList as $val) {
					if ($val['type'] == 'file') {
						$folders = explode('/', $val['filename']);
						$packageName = array_shift($folders);
						if (!isset($packageToTemplates[$packageName])) {
							$packageToTemplates[$packageName] = [];
						}
						$packageToTemplates[$packageName][] = ['index' => $val['index'], 'filename' => implode('/', $folders)];
					}
				}
				
				$knownTemplates = [];
				if ($style !== null && $style->templateGroupID) {
					$sql = "SELECT	templateName
						FROM	wcf".WCF_N."_template
						WHERE	templateGroupID = ?";
					$statement = WCF::getDB()->prepareStatement($sql);
					$statement->execute([$style->templateGroupID]);
					$knownTemplates = $statement->fetchAll(\PDO::FETCH_COLUMN);
				}
				
				// copy templates
				foreach ($packageToTemplates as $package => $templates) {
					// try to find package
					$sql = "SELECT	*
						FROM	wcf".WCF_N."_package
						WHERE	package = ?
							AND isApplication = ?";
					$statement = WCF::getDB()->prepareStatement($sql);
					$statement->execute([
						$package,
						1
					]);
					while ($row = $statement->fetchArray()) {
						// get template path
						$templatesDir = FileUtil::addTrailingSlash(FileUtil::getRealPath(WCF_DIR.$row['packageDir']).'templates/'.$templateGroupFolderName);
						
						// create template path
						if (!file_exists($templatesDir)) {
							@mkdir($templatesDir, 0777);
							FileUtil::makeWritable($templatesDir);
						}
						
						// copy templates
						foreach ($templates as $template) {
							if (!StringUtil::endsWith($template['filename'], '.tpl')) {
								continue;
							}
							
							$templatesTar->extract($template['index'], $templatesDir.$template['filename']);
							
							$templateName = str_replace('.tpl', '', $template['filename']);
							if (!in_array($templateName, $knownTemplates)) {
								TemplateEditor::create([
									'application' => Package::getAbbreviation($package),
									'packageID' => $row['packageID'],
									'templateName' => $templateName,
									'templateGroupID' => $styleData['templateGroupID']
								]);
							}
						}
					}
				}
				
				// delete tmp file
				$templatesTar->close();
				@unlink($destination);
			}
		}
		
		$duplicateLogo = false;
		// duplicate logo if logo matches mobile logo
		if (!empty($styleData['variables']['pageLogo']) && !empty($styleData['variables']['pageLogoMobile']) && $styleData['variables']['pageLogo'] == $styleData['variables']['pageLogoMobile']) {
			$styleData['variables']['pageLogoMobile'] = 'm-'.basename($styleData['variables']['pageLogo']);
			$duplicateLogo = true;
		}
		
		// save style
		if ($style === null) {
			$styleData['packageID'] = $packageID;
			$style = new StyleEditor(self::create($styleData));
			
			// handle descriptions
			if (!empty($data['description'])) {
				self::saveLocalizedDescriptions($style, $data['description']);
				LanguageFactory::getInstance()->deleteLanguageCache();
			}
			
			if ($data['default']) {
				$style->setAsDefault();
			}
		}
		else {
			unset($styleData['styleName']);
			
			$variables = $style->getVariables();
			if (!isset($styleData['variables']['individualScss'])) $styleData['variables']['individualScss'] = '';
			if (!isset($styleData['variables']['overrideScss'])) $styleData['variables']['overrideScss'] = '';
			
			$individualScss = Style::splitLessVariables($variables['individualScss']);
			$variables['individualScss'] = Style::joinLessVariables($styleData['variables']['individualScss'], $individualScss['custom']);
			unset($styleData['variables']['individualScss']);
			
			$overrideScss = Style::splitLessVariables($variables['overrideScss']);
			$variables['overrideScss'] = Style::joinLessVariables($styleData['variables']['overrideScss'], $overrideScss['custom']);
			unset($styleData['variables']['overrideScss']);
			
			// import variables that have not been explicitly defined before
			$implicitVariables = $style->getImplicitVariables();
			foreach ($styleData['variables'] as $variableName => $variableValue) {
				if (in_array($variableName, $implicitVariables)) {
					$variables[$variableName] = $variableValue;
				}
			}
			
			$styleData['variables'] = $variables;
			
			$style->update($styleData);
		}
		
		// import images
		if (!empty($data['images'])) {
			$index = $tar->getIndexByFilename($data['images']);
			if ($index !== false) {
				// extract images tar
				$destination = FileUtil::getTemporaryFilename('images_');
				$tar->extract($index, $destination);
				
				// open images tar
				$imagesTar = new Tar($destination);
				$contentList = $imagesTar->getContentList();
				foreach ($contentList as $key => $val) {
					if ($val['type'] == 'file') {
						$path = FileUtil::getRealPath($val['filename']);
						$fileExtension = pathinfo($path, PATHINFO_EXTENSION);

						if (!in_array($fileExtension, self::VALID_IMAGE_EXTENSIONS)) {
							continue;
						}
						
						if (strpos($path, '../') !== false) {
							continue;
						}
						
						$targetFile = FileUtil::getRealPath($style->getAssetPath().$path);
						if (strpos(FileUtil::getRelativePath($style->getAssetPath(), $targetFile), '../') !== false) {
							continue;
						}
						
						// duplicate pageLogo for mobile version
						if ($duplicateLogo && $val['filename'] == $styleData['variables']['pageLogo']) {
							$imagesTar->extract($key, $style->getAssetPath().'m-'.basename($targetFile));
						}
						
						$imagesTar->extract($key, $targetFile);
						FileUtil::makeWritable($targetFile);
					}
				}
				
				// delete tmp file
				$imagesTar->close();
				@unlink($destination);
			}
		}
		
		// import preview image
		foreach (['image', 'image2x'] as $type) {
			if (!empty($data[$type])) {
				$fileExtension = pathinfo($data[$type], PATHINFO_EXTENSION);
				if (!in_array($fileExtension, self::VALID_IMAGE_EXTENSIONS)) {
					continue;
				}
				
				$index = $tar->getIndexByFilename($data[$type]);
				if ($index !== false) {
					$filename = $style->getAssetPath().'stylePreview' . ($type === 'image2x' ? '@2x' : '') . '.' . $fileExtension;
					$tar->extract($index, $filename);
					FileUtil::makeWritable($filename);
					
					if (file_exists($filename)) {
						try {
							if (($imageData = getimagesize($filename)) !== false) {
								switch ($imageData[2]) {
									case IMAGETYPE_PNG:
									case IMAGETYPE_JPEG:
									case IMAGETYPE_GIF:
										$style->update([$type => 'style-' . $style->styleID . '/stylePreview' . ($type === 'image2x' ? '@2x' : '') . '.' . $fileExtension]);
								}
							}
						}
						catch (SystemException $e) {
							// broken image
						}
					}
				}
			}
		}
		
		// import cover photo
		if (!empty($data['coverPhoto'])) {
			$fileExtension = pathinfo($data['coverPhoto'], PATHINFO_EXTENSION);
			$index = $tar->getIndexByFilename($data['coverPhoto']);
			if ($index !== false && in_array($fileExtension, self::VALID_IMAGE_EXTENSIONS)) {
				$filename = $style->getAssetPath().'coverPhoto.' . $fileExtension;
				$tar->extract($index, $filename);
				FileUtil::makeWritable($filename);
				
				if (file_exists($filename)) {
					try {
						if (($imageData = getimagesize($filename)) !== false) {
							switch ($imageData[2]) {
								case IMAGETYPE_PNG:
								case IMAGETYPE_JPEG:
								case IMAGETYPE_GIF:
									$style->update(['coverPhotoExtension' => $fileExtension]);
							}
						}
					}
					catch (SystemException $e) {
						// broken image
					}
				}
			}
		}
		
		if (!$skipFontDownload) {
			// download google fonts
			$fontManager = FontManager::getInstance();
			$family = $style->getVariable('wcfFontFamilyGoogle');
			try {
				$fontManager->downloadFamily($family);
			}
			catch (FontDownloadFailed $e) {
				// ignore
			}
		}

		$tar->close();
		
		return $style;
	}
	
	/**
	 * Saves localized style descriptions.
	 * 
	 * @param	StyleEditor	$styleEditor
	 * @param	string[]	$descriptions
	 */
	protected static function saveLocalizedDescriptions(StyleEditor $styleEditor, array $descriptions) {
		// localize package information
		$sql = "REPLACE INTO	wcf".WCF_N."_language_item
					(languageID, languageItem, languageItemValue, languageCategoryID, packageID)
			VALUES		(?, ?, ?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		// get language list
		$languageList = new LanguageList();
		$languageList->readObjects();
		
		// workaround for WCFSetup
		if (!PACKAGE_ID) {
			$sql = "SELECT	*
				FROM	wcf".WCF_N."_language_category
				WHERE	languageCategory = ?";
			$statement2 = WCF::getDB()->prepareStatement($sql);
			$statement2->execute(['wcf.style']);
			$languageCategory = $statement2->fetchObject(LanguageCategory::class);
		}
		else {
			$languageCategory = LanguageFactory::getInstance()->getCategory('wcf.style');
		}
		
		foreach ($languageList as $language) {
			if (isset($descriptions[$language->languageCode])) {
				$statement->execute([
					$language->languageID,
					'wcf.style.styleDescription'.$styleEditor->styleID,
					$descriptions[$language->languageCode],
					$languageCategory->languageCategoryID,
					$styleEditor->packageID
				]);
			}
		}
		
		$styleEditor->update([
			'styleDescription' => 'wcf.style.styleDescription'.$styleEditor->styleID
		]);
	}
	
	/**
	 * Returns available location path.
	 * 
	 * @param	string		$location
	 * @return	string
	 */
	protected static function getFileLocation($location) {
		$location = FileUtil::removeLeadingSlash(FileUtil::removeTrailingSlash($location));
		$location = WCF_DIR.$location;
		
		$index = null;
		do {
			$directory = $location . ($index === null ? '' : $index);
			if (!is_dir($directory)) {
				@mkdir($directory, 0777, true);
				FileUtil::makeWritable($directory);
				
				return FileUtil::addTrailingSlash($directory);
			}
			
			$index = ($index === null ? 2 : ($index + 1));
		}
		while (true);
		
		// this should never happen
		throw new \LogicException();
	}
	
	/**
	 * Exports this style.
	 * 
	 * @param	boolean		$templates
	 * @param	boolean		$images
	 * @param	string		$packageName
	 */
	public function export($templates = false, $images = false, $packageName = '') {
		// create style tar
		$styleTarName = FileUtil::getTemporaryFilename('style_', '.tgz');
		$styleTar = new TarWriter($styleTarName, true);
		
		// append style preview image
		if ($this->image && @file_exists(WCF_DIR.'images/'.$this->image)) {
			$styleTar->add(WCF_DIR.'images/'.$this->image, '', FileUtil::addTrailingSlash(dirname(WCF_DIR.'images/'.$this->image)));
		}
		if ($this->image2x && @file_exists(WCF_DIR.'images/'.$this->image2x)) {
			$styleTar->add(WCF_DIR.'images/'.$this->image2x, '', FileUtil::addTrailingSlash(dirname(WCF_DIR.'images/'.$this->image2x)));
		}
		
		// append cover photo
		$coverPhoto = ($this->coverPhotoExtension) ? $this->getAssetPath().'coverPhoto.'.$this->coverPhotoExtension : '';
		if ($coverPhoto && @file_exists($coverPhoto)) {
			$styleTar->add($coverPhoto, '', FileUtil::addTrailingSlash(dirname($coverPhoto)));
		}
		
		// fetch style description
		$sql = "SELECT		language.languageCode, language_item.languageItemValue
			FROM		wcf".WCF_N."_language_item language_item
			LEFT JOIN	wcf".WCF_N."_language language
			ON		(language.languageID = language_item.languageID)
			WHERE		language_item.languageItem = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$this->styleDescription]);
		$styleDescriptions = $statement->fetchMap('languageCode', 'languageItemValue');
		
		// create style info file
		$xml = new XMLWriter();
		$xml->beginDocument('style', 'http://www.woltlab.com', 'http://www.woltlab.com/XSD/' . WSC_API_VERSION . '/style.xsd');
		
		// general block
		$xml->startElement('general');
		$xml->writeElement('stylename', $this->styleName);
		$xml->writeElement('packageName', $this->packageName);
		
		// style description
		foreach ($styleDescriptions as $languageCode => $value) {
			$xml->writeElement('description', $value, ['language' => $languageCode]);
		}
		
		$xml->writeElement('date', $this->styleDate);
		$xml->writeElement('version', $this->styleVersion);
		$xml->writeElement('apiVersion', $this->apiVersion);
		if ($this->image) $xml->writeElement('image', basename($this->image));
		if ($this->image2x) $xml->writeElement('image2x', basename($this->image2x));
		if ($coverPhoto) $xml->writeElement('coverPhoto', basename(FileUtil::unifyDirSeparator($coverPhoto)));
		if ($this->copyright) $xml->writeElement('copyright', $this->copyright);
		if ($this->license) $xml->writeElement('license', $this->license);
		$xml->endElement();
		
		// author block
		$xml->startElement('author');
		$xml->writeElement('authorname', $this->authorName);
		if ($this->authorURL) $xml->writeElement('authorurl', $this->authorURL);
		$xml->endElement();
		
		// files block
		$xml->startElement('files');
		$xml->writeElement('variables', 'variables.xml');
		if ($templates) $xml->writeElement('templates', 'templates.tar');
		if ($images) $xml->writeElement('images', 'images.tar', ['path' => $this->imagePath]);
		$xml->endElement();
		
		// append style info file to style tar
		$styleTar->addString(self::INFO_FILE, $xml->endDocument());
		
		// create variable list
		$xml->beginDocument('variables', 'http://www.woltlab.com', 'http://www.woltlab.com/XSD/' . WSC_API_VERSION . '/styleVariables.xsd');
		
		// get variables
		$sql = "SELECT		variable.variableName, value.variableValue
			FROM		wcf".WCF_N."_style_variable_value value
			LEFT JOIN	wcf".WCF_N."_style_variable variable
			ON		(variable.variableID = value.variableID)
			WHERE		value.styleID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$this->styleID]);
		while ($row = $statement->fetchArray()) {
			$xml->writeElement('variable', $row['variableValue'], ['name' => $row['variableName']]);
		}
		
		// append variable list to style tar
		$styleTar->addString('variables.xml', $xml->endDocument());
		
		if ($templates && $this->templateGroupID) {
			$templateGroup = new TemplateGroup($this->templateGroupID);
			
			// create templates tar
			$templatesTarName = FileUtil::getTemporaryFilename('templates', '.tar');
			$templatesTar = new TarWriter($templatesTarName);
			FileUtil::makeWritable($templatesTarName);
			
			// append templates to tar
			// get templates
			$sql = "SELECT		template.*, package.package
				FROM		wcf".WCF_N."_template template
				LEFT JOIN	wcf".WCF_N."_package package
				ON		(package.packageID = template.packageID)
				WHERE		template.templateGroupID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$this->templateGroupID]);
			while ($row = $statement->fetchArray()) {
				$packageDir = 'com.woltlab.wcf';
				$package = null;
				
				if (Template::isSystemCritical($row['templateName'])) {
					continue;
				}
				
				if ($row['application'] != 'wcf') {
					$application = ApplicationHandler::getInstance()->getApplication($row['application']);
					$package = PackageCache::getInstance()->getPackage($application->packageID);
					$packageDir = $package->package;
				}
				else {
					$application = ApplicationHandler::getInstance()->getWCF();
					$package = PackageCache::getInstance()->getPackage($application->packageID);
				}
				
				$filename = FileUtil::addTrailingSlash(FileUtil::getRealPath(WCF_DIR . $package->packageDir . 'templates/' . $templateGroup->templateGroupFolderName)) . $row['templateName'] . '.tpl';
				$templatesTar->add($filename, $packageDir, dirname($filename));
			}
			
			// append templates tar to style tar
			$templatesTar->create();
			$styleTar->add($templatesTarName, 'templates.tar', $templatesTarName);
			@unlink($templatesTarName);
		}
		
		if ($images) {
			// create images tar
			$imagesTarName = FileUtil::getTemporaryFilename('images_', '.tar');
			$imagesTar = new TarWriter($imagesTarName);
			FileUtil::makeWritable($imagesTarName);
			
			$regEx = new Regex('\.(jpg|jpeg|gif|png|svg|ico|json|xml|txt)$', Regex::CASE_INSENSITIVE);
			$iterator = new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator(
					$this->getAssetPath(),
					\FilesystemIterator::SKIP_DOTS
				), 
				\RecursiveIteratorIterator::SELF_FIRST
			);
			foreach ($iterator as $file) {
				/** @var \SplFileInfo $file */
				if (!$file->isFile()) continue;
				if (!$regEx->match($file->getPathName())) continue;
				
				$imagesTar->add($file->getPathName(), '', $this->getAssetPath());
			}
			// append images tar to style tar
			$imagesTar->create();
			$styleTar->add($imagesTarName, 'images.tar', $imagesTarName);
			@unlink($imagesTarName);
		}
		
		// output file content
		$styleTar->create();
		
		// export as style package
		if (empty($packageName)) {
			readfile($styleTarName);
		}
		else {
			// export as package
			
			// create package tar
			$packageTarName = FileUtil::getTemporaryFilename('package_', '.tar.gz');
			$packageTar = new TarWriter($packageTarName, true);
			
			// append style tar
			$styleTarName = FileUtil::unifyDirSeparator($styleTarName);
			$packageTar->add($styleTarName, '', FileUtil::addTrailingSlash(dirname($styleTarName)));
			
			// create package.xml
			$xml->beginDocument('package', 'http://www.woltlab.com', 'http://www.woltlab.com/XSD/' . WSC_API_VERSION . '/package.xsd', ['name' => $packageName]);
			
			$xml->startElement('packageinformation');
			$xml->writeElement('packagename', $this->styleName);
			
			// description
			foreach ($styleDescriptions as $languageCode => $value) {
				$xml->writeElement('packagedescription', $value, ['language' => $languageCode]);
			}
			
			$xml->writeElement('version', $this->styleVersion);
			$xml->writeElement('date', $this->styleDate);
			$xml->endElement();
			
			$xml->startElement('authorinformation');
			$xml->writeElement('author', $this->authorName);
			if ($this->authorURL) $xml->writeElement('authorurl', $this->authorURL);
			$xml->endElement();
			
			$xml->startElement('requiredpackages');
			$xml->writeElement('requiredpackage', 'com.woltlab.wcf', ['minversion' => PackageCache::getInstance()->getPackageByIdentifier('com.woltlab.wcf')->packageVersion]);
			$xml->endElement();
			
			$xml->startElement('excludedpackages');
			$xml->writeElement('excludedpackage', 'com.woltlab.wcf', ['version' => self::EXCLUDE_WCF_VERSION]);
			$xml->endElement();
			
			// @deprecated 5.2
			$xml->startElement('compatibility');
			foreach (self::$compatibilityApiVersions as $apiVersion) {
				$xml->writeElement('api', '', ['version' => $apiVersion]);
			}
			$xml->endElement();
			
			$xml->startElement('instructions', ['type' => 'install']);
			$xml->writeElement('instruction', basename($styleTarName), ['type' => 'style']);
			$xml->endElement();
			
			// append package info file to package tar
			$packageTar->addString(PackageArchive::INFO_FILE, $xml->endDocument());
			
			$packageTar->create();
			readfile($packageTarName);
			@unlink($packageTarName);
		}
		
		@unlink($styleTarName);
	}
	
	/**
	 * Sets the variables of a style.
	 * 
	 * @param	string[]		$variables
	 */
	public function setVariables(array $variables = []) {
		// delete old variables
		$sql = "DELETE FROM	wcf".WCF_N."_style_variable_value
			WHERE		styleID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$this->styleID]);
		
		// insert new variables
		if (!empty($variables)) {
			$sql = "SELECT	*
				FROM	wcf".WCF_N."_style_variable";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute();
			$styleVariables = [];
			while ($row = $statement->fetchArray()) {
				$variableName = $row['variableName'];
				
				if (isset($variables[$variableName])) {
					// compare value, save only if differs from default
					if ($variables[$variableName] != $row['defaultValue']) {
						$styleVariables[$row['variableID']] = $variables[$variableName];
					}
				}
			}
			
			if (!empty($styleVariables)) {
				$sql = "INSERT INTO	wcf".WCF_N."_style_variable_value
							(styleID, variableID, variableValue)
					VALUES		(?, ?, ?)";
				$statement = WCF::getDB()->prepareStatement($sql);
				
				WCF::getDB()->beginTransaction();
				foreach ($styleVariables as $variableID => $variableValue) {
					$statement->execute([
						$this->styleID,
						$variableID,
						$variableValue
					]);
				}
				WCF::getDB()->commitTransaction();
			}
		}
		
		$this->writeStyleFile();
	}
	
	/**
	 * Writes the style-*.css file.
	 */
	public function writeStyleFile() {
		StyleCompiler::getInstance()->compile($this->getDecoratedObject());
	}
	
	/**
	 * @inheritDoc
	 * @return	Style
	 */
	public static function create(array $parameters = []) {
		$variables = null;
		if (isset($parameters['variables'])) {
			$variables = $parameters['variables'];
			unset($parameters['variables']);
		}
		
		// default values
		if (!isset($parameters['packageID'])) $parameters['packageID'] = 1;
		if (!isset($parameters['styleDate'])) $parameters['styleDate'] = gmdate('Y-m-d', TIME_NOW);
		
		// check if no default style is defined
		$sql = "SELECT	styleID
			FROM	wcf".WCF_N."_style
			WHERE	isDefault = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([1]);
		$row = $statement->fetchArray();
		
		// no default style exists
		if ($row === false) {
			$parameters['isDefault'] = 1;
		}
		
		/** @var Style $style */
		$style = parent::create($parameters);
		$styleEditor = new StyleEditor($style);
		
		// create asset path
		FileUtil::makePath($style->getAssetPath());
		
		$styleEditor->update([
			'imagePath' => FileUtil::getRelativePath(WCF_DIR, $style->getAssetPath()),
		]);
		
		// save variables
		if ($variables !== null) {
			$styleEditor->setVariables($variables);
		}
		
		return $style;
	}
	
	/**
	 * @inheritDoc
	 */
	public static function resetCache() {
		StyleCacheBuilder::getInstance()->reset();
	}
}
