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
use wcf\system\io\File;
use wcf\system\io\Tar;
use wcf\system\io\TarWriter;
use wcf\system\style\StyleCompiler;
use wcf\system\WCF;
use wcf\util\DateUtil;
use wcf\util\FileUtil;
use wcf\util\StringUtil;
use wcf\util\StyleUtil;
use wcf\util\XML;

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
	const STYLE_PREVIEW_IMAGE_MAX_WIDTH = 185;
	const STYLE_PREVIEW_IMAGE_MAX_HEIGHT = 140;
	
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
			self::scalePreviewImage(WCF_DIR.$parameters['image']);
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
		@unlink(WCF_DIR.'style/style-'.$this->styleID.'.css');
		@unlink(WCF_DIR.'style/style-'.$this->styleID.'-rtl.css');
		
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
			'variables' => '', 'date' => '0000-00-00', 'icons' => ''
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
		$iconsLocation = FileUtil::addTrailingSlash($data['variables']['global.icons.location']);
		$imagesLocation = $data['variables']['global.images.location'];
		
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
				$templateGroupName = $originalTemplateGroupName . '_' . $i; //TODO: undefined variable
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
			'image' => ($data['image'] ? 'images/' : '').$data['image'],
			'copyright' => $data['copyright'],
			'license' => $data['license'],
			'authorName' => $data['authorName'],
			'authorURL' => $data['authorURL']
		);
		if ($style !== null) {
			$style->update($styleData);
		}
		else {
			$styleData['packageID'] = $packageID;
			$style = self::create($styleData);
		}
		
		// import preview image
		if (!empty($data['image'])) {
			$i = $tar->getIndexByFilename($data['image']);
			if ($i !== false) {
				$tar->extract($i, WCF_DIR.'images/'.$data['image']);
				@chmod(WCF_DIR.'images/'.$data['image'], 0777);
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
	 */
	public function export($templates = false, $images = false, $icons = false) {
		// create style tar
		$styleTarName = FileUtil::getTemporaryFilename('style_', '.tgz');
		$styleTar = new TarWriter($styleTarName, true);
		
		// append style preview image
		if ($this->image && @file_exists(WCF_DIR.$this->image)) {
			$styleTar->add(WCF_DIR.$this->image, '', FileUtil::addTrailingSlash(dirname(WCF_DIR.$this->image)));
		}
		
		// create style info file
		$string = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n<!DOCTYPE style SYSTEM \"http://www.woltlab.com/DTDs/SXF/style.dtd\">\n<style>\n";
		
		// general block
		$string .= "\t<general>\n";
		$string .= "\t\t<stylename><![CDATA[".StringUtil::escapeCDATA((CHARSET != 'UTF-8' ? StringUtil::convertEncoding(CHARSET, 'UTF-8', $this->styleName) : $this->styleName))."]]></stylename>\n"; // style name
		if ($this->styleDescription) $string .= "\t\t<description><![CDATA[".StringUtil::escapeCDATA((CHARSET != 'UTF-8' ? StringUtil::convertEncoding(CHARSET, 'UTF-8', $this->styleDescription) : $this->styleDescription))."]]></description>\n"; // style description
		if ($this->styleVersion) $string .= "\t\t<version><![CDATA[".StringUtil::escapeCDATA((CHARSET != 'UTF-8' ? StringUtil::convertEncoding(CHARSET, 'UTF-8', $this->styleVersion) : $this->styleVersion))."]]></version>\n"; // style version
		if ($this->styleDate) $string .= "\t\t<date><![CDATA[".StringUtil::escapeCDATA((CHARSET != 'UTF-8' ? StringUtil::convertEncoding(CHARSET, 'UTF-8', $this->styleDate) : $this->styleDate))."]]></date>\n"; // style date
		if ($this->image) $string .= "\t\t<image><![CDATA[".StringUtil::escapeCDATA((CHARSET != 'UTF-8' ? StringUtil::convertEncoding(CHARSET, 'UTF-8', basename($this->image)) : basename($this->image)))."]]></image>\n"; // style preview image
		if ($this->copyright) $string .= "\t\t<copyright><![CDATA[".StringUtil::escapeCDATA((CHARSET != 'UTF-8' ? StringUtil::convertEncoding(CHARSET, 'UTF-8', $this->copyright) : $this->copyright))."]]></copyright>\n"; // copyright
		if ($this->license) $string .= "\t\t<license><![CDATA[".StringUtil::escapeCDATA((CHARSET != 'UTF-8' ? StringUtil::convertEncoding(CHARSET, 'UTF-8', $this->license) : $this->license))."]]></license>\n"; // license
		$string .= "\t</general>\n";
		
		// author block
		$string .= "\t<author>\n";
		if ($this->authorName) $string .= "\t\t<authorname><![CDATA[".StringUtil::escapeCDATA((CHARSET != 'UTF-8' ? StringUtil::convertEncoding(CHARSET, 'UTF-8', $this->authorName) : $this->authorName))."]]></authorname>\n"; // author name
		if ($this->authorURL) $string .= "\t\t<authorurl><![CDATA[".StringUtil::escapeCDATA((CHARSET != 'UTF-8' ? StringUtil::convertEncoding(CHARSET, 'UTF-8', $this->authorURL) : $this->authorURL))."]]></authorurl>\n"; // author URL
		$string .= "\t</author>\n";
		
		// files block
		$string .= "\t<files>\n";
		$string .= "\t\t<variables>variables.xml</variables>\n"; // variables
		if ($templates && $this->templateGroupID) $string .= "\t\t<templates>templates.tar</templates>\n"; // templates
		if ($images) $string .= "\t\t<images>images.tar</images>\n"; // images
		if ($icons) $string .= "\t\t<icons>icons.tar</icons>\n"; // icons
		$string .= "\t</files>\n";
		
		$string .= "</style>";
		// append style info file to style tar
		$styleTar->addString(self::INFO_FILE, $string);
		unset($string);
		
		// create variable list
		$string = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n<!DOCTYPE variables SYSTEM \"http://www.woltlab.com/DTDs/SXF/variables.dtd\">\n<variables>\n";
		// get variables
		$variables = $this->getVariables();
		$exportImages = array();
		foreach ($variables as $name => $value) {
			// search images
			if ($images && $value) {
				if (preg_match_all('~([^/\s\$]+\.(?:gif|jpg|jpeg|png))~i', $value, $matches)) {
					$exportImages = array_merge($exportImages, $matches[1]);
				}
			}
			$string .= "\t<variable name=\"".StringUtil::encodeHTML($name)."\"><![CDATA[".StringUtil::escapeCDATA((CHARSET != 'UTF-8' ? StringUtil::convertEncoding(CHARSET, 'UTF-8', $value) : $value))."]]></variable>\n";
		}
		
		$string .= "</variables>";
		// append variable list to style tar
		$styleTar->addString('variables.xml', $string);
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

		if ($images) {
			// create images tar
			$imagesTarName = FileUtil::getTemporaryFilename('images_', '.tar');
			$imagesTar = new TarWriter($imagesTarName);
			@chmod($imagesTarName, 0777);
			
			// cache rtl versions
			foreach ($exportImages as $exportImage) {
				if (strpos($exportImage, '-ltr')) $exportImages[] = str_replace('-ltr', '-rtl', $exportImage);
			}
			
			// append images to tar
			$path = WCF_DIR.$variables['global.images.location'];
			if (file_exists($path) && is_dir($path)) {
				$handle = opendir($path);
				
				while (($file = readdir($handle)) !== false) {
					if (is_file($path.$file) && in_array($file, $exportImages)) {
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
		$iconsLocation = FileUtil::addTrailingSlash($variables['global.icons.location']);
		if ($icons && $iconsLocation != 'icon/') {
			// create icons tar
			$iconsTarName = FileUtil::getTemporaryFilename('icons_', '.tar');
			$iconsTar = new TarWriter($iconsTarName);
			@chmod($iconsTar, 0777);
			
			// get package dirs
			$sql = "SELECT	package, packageDir
				FROM	wcf".WCF_N."_package
				WHERE	isApplication = 1
					AND (packageDir <> '' OR package = 'com.woltlab.wcf')";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute();
			while ($row = $statement->fetchArray()) {
				$iconsDir = FileUtil::getRealPath(WCF_DIR.$row['packageDir']).$iconsLocation;
				$packageIcons = array();
				if (file_exists($iconsDir)) {
					$icons = glob($iconsDir.'*.png');
					if (is_array($icons)) {
						foreach ($icons as $icon) {
							$packageIcons[] = $icon;
						}
					}
				}
				
				if (count($packageIcons)) {
					$iconsTar->add($packageIcons, $row['package'].'/', $iconsDir);
				}
			}
			
			$iconsTar->create();
			$styleTar->add($iconsTarName, 'icons.tar', $iconsTarName);
			@unlink($iconsTarName);
		}
		
		// output file content
		$styleTar->create();
		readfile($styleTarName);
		@unlink($styleTarName);
	}
	
	/**
	 * Sets the variables of a style.
	 */
	public function setVariables($variables) {
		// delete old variables
		$sql = "DELETE FROM	wcf".WCF_N."_style_variable
			WHERE		styleID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->styleID));
		
		// insert new variables
		$statementParameters = array();
		foreach ($variables as $name => $value) {
			$statementParameters[] = array(
				'name' => $name,
				'value' => $value
			);
		}
		
		if (count($statementParameters)) {
			$sql = "INSERT INTO	wcf".WCF_N."_style_variable
						(styleID, variableName, variableValue)
				VALUES		(?, ?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			foreach ($statementParameters as $parameter) {
				$statement->execute(array(
					$this->styleID,
					$parameter['name'],
					$parameter['value']
				));
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
		CacheHandler::getInstance()->clear(WCF_DIR.'cache', 'cache.icon-*-*.php');
	}
	
	/**
	 * Scales the style preview image.
	 * 
	 * @param	string		$filename
	 */
	public static function scalePreviewImage($filename) {
		$adapter = ImageHandler::getInstance()->getAdapter();
		$adapter->load($filename);
		$thumbnail = $adapter->createThumbnail(self::STYLE_PREVIEW_IMAGE_MAX_WIDTH, self::STYLE_PREVIEW_IMAGE_MAX_HEIGHT);
		$adapter->writeImage($thumbnail, $filename);
	}
	
	private static $variables = array();
	private static function parseAdditionalStyles(&$variables) {
		self::$variables = $variables;
		// fix images location
		if (!empty(self::$variables['global.images.location']) && !FileUtil::isURL(self::$variables['global.images.location']) && substr(self::$variables['global.images.location'], 0, 1) != '/') {
			self::$variables['global.images.location'] = '../'.self::$variables['global.images.location'];
		}
		// fix images location
		if (!empty(self::$variables['global.icons.location']) && !FileUtil::isURL(self::$variables['global.icons.location']) && substr(self::$variables['global.icons.location'], 0, 1) != '/') {
			self::$variables['global.icons.location'] = '../'.self::$variables['global.icons.location'];
		}
		
		// parse additional styles
		if (!empty($variables['user.additional.style.input1.use'])) {
			$variables['user.additional.style.input1.use'] = preg_replace_callback('/\$([a-z0-9_\-\.]+)\$/', array('self', 'parseAdditionalStylesCallback'), $variables['user.additional.style.input1.use']);
		}
		if (!empty($variables['user.additional.style.input2.use'])) {
			$variables['user.additional.style.input2.use'] = preg_replace_callback('/\$([a-z0-9_\-\.]+)\$/', array('self', 'parseAdditionalStylesCallback'), $variables['user.additional.style.input2.use']);
		}
	}
	
	private static function parseAdditionalStylesCallback($match) {
		if (isset(self::$variables[$match[1]])) {
			return self::$variables[$match[1]];
		}
		else {
			return $match[0];
		}
	}
}
