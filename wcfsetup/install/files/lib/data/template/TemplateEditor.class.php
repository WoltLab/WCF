<?php
namespace wcf\data\template;
use wcf\data\DatabaseObjectEditor;
use wcf\system\io\File;
use wcf\system\Regex;
use wcf\system\WCF;
use wcf\util\DirectoryUtil;
use wcf\util\FileUtil;

/**
 * Provides functions to edit templates.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Template
 * 
 * @method	Template	getDecoratedObject()
 * @mixin	Template
 */
class TemplateEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Template::class;
	
	/**
	 * @inheritDoc
	 */
	public static function create(array $parameters = []) {
		// obtain default values
		if (!isset($parameters['packageID'])) $parameters['packageID'] = PACKAGE_ID;
		if (!isset($parameters['lastModificationTime'])) $parameters['lastModificationTime'] = TIME_NOW;
		
		return parent::create($parameters);
	}
	
	/**
	 * Saves the source of this template.
	 * 
	 * @param	string		$source
	 */
	public function setSource($source) {
		$path = $this->getPath();
		// create dir
		$folder = dirname($path);
		if (!file_exists($folder)) {
			mkdir($folder, 0777);
		}
		
		// set source
		$file = new File($path);
		$file->write($source);
		$file->close();
		FileUtil::makeWritable($path);
	}
	
	/**
	 * Renames the file of this template.
	 * 
	 * @param	string		$name
	 * @param	integer		$templateGroupID
	 */
	public function rename($name, $templateGroupID = 0) {
		// get current path
		$currentPath = $this->getPath();
		
		// get new path
		if ($templateGroupID != $this->templateGroupID) {
			// get folder name
			$sql = "SELECT	templateGroupFolderName
				FROM	wcf".WCF_N."_template_group
				WHERE	templateGroupID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$templateGroupID]);
			$row = $statement->fetchArray();
			$this->object->data['templateGroupFolderName'] = $row['templateGroupFolderName'];
		}
		
		// delete compiled templates
		$this->deleteCompiledFiles();
		
		// rename
		$this->object->data['templateName'] = $name;
		$newPath = $this->getPath();
		
		// move file
		@rename($currentPath, $newPath);
	}
	
	/**
	 * Deletes this template.
	 */
	public function delete() {
		$this->deleteFile();
		
		$sql = "DELETE FROM	wcf".WCF_N."_template
			WHERE		templateID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$this->templateID]);
	}
	
	/**
	 * Deletes the file of this template.
	 */
	public function deleteFile() {
		// delete source
		@unlink($this->getPath());
		
		// delete compiled templates
		$this->deleteCompiledFiles();
	}
	
	/**
	 * @inheritDoc
	 */
	public static function deleteAll(array $objectIDs = []) {
		$list = new TemplateList();
		$list->setObjectIDs($objectIDs);
		$list->readObjects();
		foreach ($list as $template) {
			$editor = new TemplateEditor($template);
			$editor->deleteFile();
		}
		
		return parent::deleteAll($objectIDs);
	}
	
	/**
	 * Deletes the compiled files of this template.
	 */
	public function deleteCompiledFiles() {
		DirectoryUtil::getInstance(WCF_DIR . 'templates/compiled/')->removePattern(new Regex($this->templateGroupID . '_' . $this->application . '_.*_' . preg_quote($this->templateName) . '.php$'));
	}
}
