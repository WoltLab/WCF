<?php
namespace wcf\data\style;
use wcf\data\package\Package;
use wcf\data\template\group\TemplateGroup;
use wcf\data\template\group\TemplateGroupEditor;
use wcf\data\template\TemplateEditor;
use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\cache\CacheHandler;
use wcf\system\exception\SystemException;
use wcf\system\image\ImageHandler;
use wcf\system\io\Tar;
use wcf\system\io\TarWriter;
use wcf\system\package\PackageArchive;
use wcf\system\style\StyleCompiler;
use wcf\system\Regex;
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
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.style
 * @category 	Community Framework
 */
class StyleEditor extends DatabaseObjectEditor implements IEditableCachedObject {
	const INFO_FILE = 'style.xml';
	
	/**
	 * @see	wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\style\Style';
	
	/**
	 * @see wcf\data\IEditableObject::update()
	 */
	public function update(array $parameters = array()) {
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
		
		// scale preview image
		if (!empty($parameters['image']) && $parameters['image'] != $this->image) {
			self::scalePreviewImage($parameters['image']);
		}
	}
	
	/**
	 * @see wcf\data\IEditableObject::delete()
	 */
	public function delete() {
		parent::delete();
		
		// delete variables
		$sql = "DELETE FROM	wcf".WCF_N."_style_variable
			WHERE		styleID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->styleID));
		
		// delete style to package
		$sql = "DELETE FROM	wcf".WCF_N."_style_to_package
			WHERE		styleID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->styleID));
		
		// delete style files
		$files = @glob(WCF_DIR.'style/style-*-'.$this->styleID.'*.css');
		if (is_array($files)) {
			foreach ($files as $file) {
				@unlink($file);
			}
		}
		
		// delete preview image
		if ($this->image) {
			@unlink(WCF_DIR.$this->image);
		}
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
		$statement->execute(array(0, 1));
		
		// set new default
		$this->update(array(
			'isDefault' => 1,
			'disabled' => 0
		));
		
		self::resetCache();
	}
	
	/**
	 * Reads the data of a style exchange format file.
	 * 
	 * @param	wcf\system\io\Tar	$tar
	 * @return	array
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
		
		$data = array(
			'name' => '', 'description' => '', 'version' => '', 'image' => '', 'copyright' => '',
			'license' => '', 'authorName' => '', 'authorURL' => '', 'templates' => '', 'images' => '',
			'variables' => '', 'date' => '0000-00-00', 'icons' => '', 'iconPath' => '', 'imagePath' => ''
		);
		
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
					foreach ($elements as $element) {
						$data[$element->tagName] = $element->nodeValue;
						if ($element->hasAttribute('path')) {
							$data[$element->tagName.'Path'] = $element->getAttribute('path');
						}
					}
				break;
		
				case 'general':
					$elements = $xpath->query('child::*', $category);
					foreach ($elements as $element) {
						switch ($element->tagName) {
							case 'date':
								DateUtil::validateDate($element->nodeValue);
		
								$data['date'] = $element->nodeValue;
							break;
									
							case 'stylename':
								$data['name'] = $element->nodeValue;
							break;
									
							case 'version':
								if (!Package::isValidVersion($element->nodeValue)) {
									throw new SystemException("style version '".$element->nodeValue."' is invalid");
								}
		
								$data['version'] = $element->nodeValue;
							break;
									
							case 'copyright':
							case 'description':
							case 'image':
							case 'license':
								$data[$element->tagName] = $element->nodeValue;
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
		$i = $tar->getIndexByFilename($data['variables']);
		if ($i === false) {
			throw new SystemException("unable to find required file '".$data['variables']."' in style archive");
		}
		
		// open variables.xml
		$data['variables'] = self::readVariablesData($data['variables'], $tar->extractToString($i));
		
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
		$xpath = $xml->xpath();
		$variables = $xml->xpath()->query('/ns:variables/ns:variable');
		
		$data = array();
		foreach ($variables as $variable) {
			$data[$variable->getAttribute('name')] = $variable->nodeValue;
		}
		
		return $data;
	}
	
	/**
	 * Gets the data of a style exchange format file.
	 * 
	 * @param	string		$filename
	 * @return	array		data
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
	 * @return	StyleEditor
	 */
	public static function import($filename, $packageID = PACKAGE_ID, StyleEditor $style = null) {
		// open file
		$tar = new Tar($filename);
		
		// get style data
		$data = self::readStyleData($tar);
		
		// get image locations
		$iconsLocation = FileUtil::addTrailingSlash('icon/'.$data['iconPath']);
		$imagesLocation = FileUtil::addTrailingSlash('images/'.$data['imagePath']);
		
		// create template group
		$templateGroupID = 0;
		if (!empty($data['templates'])) {
			$templateGroupName = $originalTemplateGroupName = $data['name'];
			$templateGroupFolderName = preg_replace('/[^a-z0-9_-]/i', '', $templateGroupName);
			if (empty($templateGroupFolderName)) $templateGroupFolderName = 'generic'.StringUtil::substring(StringUtil::getRandomID(), 0, 8);
			$originalTemplateGroupFolderName = $templateGroupFolderName;
			
			// get unique template pack name
			$i = 1;
			while (true) {
				$sql = "SELECT	COUNT(*) AS count
					FROM	wcf".WCF_N."_template_group
					WHERE	templateGroupName = ?";
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute(array($templateGroupName));
				$row = $statement->fetchArray();
				if (!$row['count']) break;
				$templateGroupName = $originalTemplateGroupName . '_' . $i;
				$i++;
			}
			
			// get unique folder name
			$i = 1;
			while (true) {
				$sql = "SELECT	COUNT(*) AS count
					FROM	wcf".WCF_N."_template_group
					WHERE	templateGroupFolderName = ?
						AND parentTemplatePackID = ?";
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute(array(
					FileUtil::addTrailingSlash($templateGroupFolderName),
					0
				));
				$row = $statement->fetchArray();
				if (!$row['count']) break;
				$templateGroupFolderName = $originalTemplateGroupFolderName . '_' . $i;
				$i++;
			}
			
			$templateGroup = TemplateGroupEditor::create(array(
				'templateGroupName' => $templateGroupName,
				'templateGroupFolderName' => FileUtil::addTrailingSlash($templateGroupFolderName)
			));
			$templateGroupID = $templateGroup->templateGroupID;
		}
		
		// save style
		$styleData = array(
			'styleName' => $data['name'],
			'variables' => $data['variables'],
			'templateGroupID' => $templateGroupID,
			'styleDescription' => $data['description'],
			'styleVersion' => $data['version'],
			'styleDate' => $data['date'],
			'copyright' => $data['copyright'],
			'license' => $data['license'],
			'authorName' => $data['authorName'],
			'authorURL' => $data['authorURL'],
			'iconPath' => $data['iconPath']
		);
		if ($style !== null) {
			$style->update($styleData);
		}
		else {
			$styleData['packageID'] = $packageID;
			$style = new StyleEditor(self::create($styleData));
		}
		
		// import preview image
		if (!empty($data['image'])) {
			$fileExtension = StringUtil::substring($data['image'], StringUtil::lastIndexOf($data['image'], '.'));
			$index = $tar->getIndexByFilename($data['image']);
			if ($index !== false) {
				$filename = WCF_DIR.'images/stylePreview-'.$style->styleID.'.'.$fileExtension;
				$tar->extract($index, $filename);
				@chmod($filename, 0777);
				
				if (file_exists($filename)) {
					$style->update(array('image' => $filename));
				}
			}
		}
		
		// import images
		if (!empty($data['images'])) {
			// create images folder if necessary
			if (!file_exists(WCF_DIR.$imagesLocation)) {
				@mkdir(WCF_DIR.$data['variables']['global.images.location'], 0777);
				@chmod(WCF_DIR.$data['variables']['global.images.location'], 0777);
			}
			
			$i = $tar->getIndexByFilename($data['images']);
			if ($i !== false) {
				// extract images tar
				$destination = FileUtil::getTemporaryFilename('images_');
				$tar->extract($i, $destination);
				
				// open images tar
				$imagesTar = new Tar($destination);
				$contentList = $imagesTar->getContentList();
				foreach ($contentList as $key => $val) {
					if ($val['type'] == 'file') {
						$imagesTar->extract($key, WCF_DIR.$imagesLocation.basename($val['filename']));
						@chmod(WCF_DIR.$imagesLocation.basename($val['filename']), 0666);
					}
				}

				// delete tmp file
				$imagesTar->close();
				@unlink($destination);
			}
		}
		
		// import icons
		if (!empty($data['icons']) && $iconsLocation != 'icon/') {
			$i = $tar->getIndexByFilename($data['icons']);
			if ($i !== false) {
				// extract icons tar
				$destination = FileUtil::getTemporaryFilename('icons_');
				$tar->extract($i, $destination);
				
				// open icons tar and group icons by package
				$iconsTar = new Tar($destination);
				$contentList = $iconsTar->getContentList();
				$packageToIcons = array();
				foreach ($contentList as $val) {
					if ($val['type'] == 'file') {
						$folders = explode('/', $val['filename']);
						$packageName = array_shift($folders);
						if (!isset($packageToIcons[$packageName])) {
							$packageToIcons[$packageName] = array();
						}
						$packageToIcons[$packageName][] = array('index' => $val['index'], 'filename' => implode('/', $folders));
					}
				}
				
				// copy icons
				foreach ($packageToIcons as $package => $icons) {
					// try to find package
					$sql = "SELECT	*
						FROM	wcf".WCF_N."_package
						WHERE	package = ?
							AND isApplication = ?";
					$statement = WCF::getDB()->prepareStatement($sql);
					$statement->execute(array(
						$package,
						1
					));
					while ($row = $statement->fetchArray()) {
						// get icon path
						$iconDir = FileUtil::getRealPath(WCF_DIR.$row['packageDir']).$iconsLocation;
						
						// create icon path
						if (!file_exists($iconDir)) {
							@mkdir($iconDir, 0777);
							@chmod($iconDir, 0777);
						}
						
						// copy icons
						foreach ($icons as $icon) {
							$iconsTar->extract($icon['index'], $iconDir.$icon['filename']);
						}
					}
				}
				
				// delete tmp file
				$iconsTar->close();
				@unlink($destination);
			}
		}
		
		// import templates
		if (!empty($data['templates'])) {
			$i = $tar->getIndexByFilename($data['templates']);
			if ($i !== false) {
				// extract templates tar
				$destination = FileUtil::getTemporaryFilename('templates_');
				$tar->extract($i, $destination);
				
				// open templates tar and group templates by package
				$templatesTar = new Tar($destination);
				$contentList = $templatesTar->getContentList();
				$packageToTemplates = array();
				foreach ($contentList as $val) {
					if ($val['type'] == 'file') {
						$folders = explode('/', $val['filename']);
						$packageName = array_shift($folders);
						if (!isset($packageToTemplates[$packageName])) {
							$packageToTemplates[$packageName] = array();
						}
						$packageToTemplates[$packageName][] = array('index' => $val['index'], 'filename' => implode('/', $folders));
					}
				}
				
				// copy templates
				foreach ($packageToTemplates as $package => $templates) {
					// try to find package
					$sql = "SELECT	*
						FROM	wcf".WCF_N."_package
						WHERE	package = ?
							AND isApplication = ?";
					$statement = WCF::getDB()->prepareStatement($sql);
					$statement->execute(array(
						$package,
						1
						));
					while ($row = $statement->fetchArray()) {
						// get icon path
						$templatesDir = FileUtil::addTrailingSlash(FileUtil::getRealPath(WCF_DIR.$row['packageDir']).'templates/'.$templateGroupFolderName);
						
						// create template path
						if (!file_exists($templatesDir)) {
							@mkdir($templatesDir, 0777);
							@chmod($templatesDir, 0777);
						}
						
						// copy templates
						foreach ($templates as $template) {
							$templatesTar->extract($template['index'], $templatesDir.$template['filename']);
							
							TemplateEditor::create(array(
								'packageID' => $row['packageID'],
								'templateName' => StringUtil::replace('.tpl', '', $template['filename']),
								'templateGroupID' => $templateGroupID
							));
						}
					}
				}
				
				// delete tmp file
				$templatesTar->close();
				@unlink($destination);
			}
		}

		$tar->close();
		
		return $style;
	}
	
	/**
	 * Exports this style.
	 * 
	 * @param	boolean 	$templates
	 * @param	boolean		$images
	 * @param	boolean		$icons
	 * @param	string		$packageName
	 */
	public function export($templates = false, $images = false, $icons = false, $packageName = '') {
		// create style tar
		$styleTarName = FileUtil::getTemporaryFilename('style_', '.tgz');
		$styleTar = new TarWriter($styleTarName, true);
		
		// append style preview image
		if ($this->image && @file_exists(WCF_DIR.'images/'.$this->image)) {
			$styleTar->add(WCF_DIR.'images/'.$this->image, '', FileUtil::addTrailingSlash(dirname(WCF_DIR.'images/'.$this->image)));
		}
		
		// create style info file
		$xml = new XMLWriter();
		$xml->beginDocument('style', 'http://www.woltlab.com', 'http://www.woltlab.com/XSD/maelstrom/style.xsd');
		
		// general block
		$xml->startElement('general');
		$xml->writeElement('stylename', $this->styleName);
		if ($this->styleDescription) $xml->writeElement('description', $this->styleDescription);
		$xml->writeElement('date', $this->styleDate);;
		if ($this->image) $xml->writeElement('image', $this->image);
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
		if ($images) $xml->writeElement('images', 'images.tar', array('path' => $this->imagePath));
		if ($icons) $xml->writeElement('icons', 'icons.tar', array('path' => $this->iconPath));
		$xml->endElement();
		
		// append style info file to style tar
		$styleTar->addString(self::INFO_FILE, $xml->endDocument());
		unset($string);
		
		// create variable list
		$xml->beginDocument('variables', 'http://www.woltlab.com', 'http://www.woltlab.com/XSD/maelstrom/styleVariables.xsd');
		
		// get variables
		$sql = "SELECT		variable.variableName, value.variableValue
			FROM		wcf".WCF_N."_style_variable_value value
			LEFT JOIN	wcf".WCF_N."_style_variable variable
			ON		(variable.variableID = value.variableID)
			WHERE		value.styleID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->styleID));
		while ($row = $statement->fetchArray()) {
			$xml->writeElement('variable', $row['variableValue'], array('name' => $row['variableName']));
		}
		
		// append variable list to style tar
		$styleTar->addString('variables.xml', $xml->endDocument());
		unset($string);
		
		if ($templates && $this->templateGroupID) {
			$templateGroup = new TemplateGroup($this->templateGroupID);
			
			// create templates tar
			$templatesTarName = FileUtil::getTemporaryFilename('templates', '.tar');
			$templatesTar = new TarWriter($templatesTarName);
			@chmod($templatesTarName, 0777);
			
			// append templates to tar
			// get templates
			$sql = "SELECT		template.*, package.package, package.packageDir,
						parent_package.package AS parentPackage, parent_package.packageDir AS parentPackageDir
				FROM		wcf".WCF_N."_template template
				LEFT JOIN	wcf".WCF_N."_package package
				ON		(package.packageID = template.packageID)
				LEFT JOIN	wcf".WCF_N."_package parent_package
				ON		(parent_package.packageID = package.parentPackageID)
				WHERE		template.templateGroupID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array($this->templateGroupID));
			while ($row = $statement->fetchArray()) {
				$packageDir = 'com.woltlab.wcf';
				if (!empty($row['parentPackageDir'])) $packageDir = $row['parentPackage'];
				else if (!empty($row['packageDir'])) $packageDir = $row['package'];
				
				$filename = FileUtil::addTrailingSlash(FileUtil::getRealPath(WCF_DIR . $row['packageDir'] . 'templates/' . $templateGroup->templateGroupFolderName)) . $row['templateName'] . '.tpl';
				$templatesTar->add($filename, $packageDir, dirname($filename));
			}
			
			// append templates tar to style tar
			$templatesTar->create();
			$styleTar->add($templatesTarName, 'templates.tar', $templatesTarName);
			@unlink($templatesTarName);
		}
		
		if ($images && ($this->imagePath && $this->imagePath != 'images/')) {
			// create images tar
			$imagesTarName = FileUtil::getTemporaryFilename('images_', '.tar');
			$imagesTar = new TarWriter($imagesTarName);
			@chmod($imagesTarName, 0777);
			
			// append images to tar
			$path = FileUtil::addTrailingSlash(WCF_DIR.$this->imagePath);
			if (file_exists($path) && is_dir($path)) {
				$handle = opendir($path);
				
				$regEx = new Regex('\.(jpg|jpeg|gif|png|svg)$', Regex::CASE_INSENSITIVE);
				while (($file = readdir($handle)) !== false) {
					if (is_file($path.$file) && $regEx->match($file)) {
						$imagesTar->add($path.$file, '', $path);
					}
				}
			}
			
			// append images tar to style tar
			$imagesTar->create();
			$styleTar->add($imagesTarName, 'images.tar', $imagesTarName);
			@unlink($imagesTarName);
		}
		
		// export icons
		if ($icons && ($this->iconPath && $this->iconPath != 'icon/')) {
			// create icons tar
			$iconsTarName = FileUtil::getTemporaryFilename('icons_', '.tar');
			$iconsTar = new TarWriter($iconsTarName);
			@chmod($iconsTar, 0777);
			
			// append icons to tar
			$path = FileUtil::addTrailingSlash(WCF_DIR.$this->iconPath);
			if (file_exists($path) && is_dir($path)) {
				$icons = glob($path.'*.svg');
				foreach ($icons as $icon) {
					$iconsTar->add($icon, '', $path);
				}
			}
			
			$iconsTar->create();
			$styleTar->add($iconsTarName, 'icons.tar', $iconsTarName);
			@unlink($iconsTarName);
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
			$styleTarName = FileUtil::unifyDirSeperator($styleTarName);
			$packageTar->add($styleTarName, '', dirname($styleTarName));
			
			// create package.xml
			$xml->beginDocument('package', 'http://www.woltlab.com', 'http://www.woltlab.com/XSD/maelstrom/package.xsd', array('name' => $packageName));
			
			$xml->startElement('packageinformation');
			$xml->writeElement('packagename', $this->styleName);
			$xml->writeElement('packagedescription', $this->styleDescription);
			$xml->writeElement('version', $this->styleVersion);
			$xml->writeElement('date', $this->styleDate);
			$xml->endElement();
			
			$xml->startElement('authorinformation');
			$xml->writeElement('author', $this->authorName);
			if ($this->authorURL) $xml->writeElement('authorurl', $this->authorURL);
			$xml->endElement();
			
			$xml->startElement('instructions', array('name' => 'install'));
			$xml->writeElement('instruction', basename($styleTarName), array('type' => 'style'));
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
	 * @param	array<string>		$variables
	 */
	public function setVariables(array $variables = array()) {
		// delete old variables
		$sql = "DELETE FROM	wcf".WCF_N."_style_variable_value
			WHERE		styleID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->styleID));
		
		// insert new variables
		if (!empty($variables)) {
			$sql = "SELECT	*
				FROM	wcf".WCF_N."_style_variable";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute();
			$styleVariables = array();
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
					$statement->execute(array(
						$this->styleID,
						$variableID,
						$variableValue
					));
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
	 * @see wcf\data\IEditableObject::create()
	 */
	public static function create(array $parameters = array()) {
		$variables = null;
		if (isset($parameters['variables'])) {
			$variables = $parameters['variables'];
			unset($parameters['variables']);
		}
		
		// default values
		if (!isset($parameters['packageID'])) $parameters['packageID'] = PACKAGE_ID;
		if (!isset($parameters['styleDate'])) $parameters['styleDate'] = gmdate('Y-m-d', TIME_NOW);
		
		// save style
		$style = parent::create($parameters);		
		$styleEditor = new StyleEditor($style);
		
		// save variables
		if ($variables !== null) {
			$styleEditor->setVariables($variables);
		}
		
		// scale preview image
		if (!empty($parameters['image'])) {
			self::scalePreviewImage(WCF_DIR.$parameters['image']);
		}
		
		return $style;
	}
	
	/**
	 * @see wcf\data\IEditableCachedObject::resetCache()
	 */
	public static function resetCache() {
		CacheHandler::getInstance()->clear(WCF_DIR.'cache', 'cache.styles.php');
	}
	
	/**
	 * Scales the style preview image.
	 * 
	 * @param	string		$filename
	 */
	public static function scalePreviewImage($filename) {
		$adapter = ImageHandler::getInstance()->getAdapter();
		$adapter->loadFile($filename);
		$thumbnail = $adapter->createThumbnail(Style::PREVIEW_IMAGE_MAX_WIDTH, Style::PREVIEW_IMAGE_MAX_HEIGHT);
		$adapter->writeImage($thumbnail, $filename);
	}
}
