<?php
namespace wcf\data\template;
use wcf\data\DatabaseObjectEditor;
use wcf\system\io\File;
use wcf\system\WCF;

/**
 * TemplateEditor provides functions to create, edit or delete templates. 
 * 
 * @author	Marcel Werk
 * @copyright	2001-2010 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.template
 * @category 	Community Framework
 */
class TemplateEditor extends DatabaseObjectEditor {
	/**
	 * @see	DatabaseObjectEditor::$baseClass
	 */
	protected static $baseClass = 'wcf\data\template\Template';
	
	/**
	 * @see	EditableObject::create()
	 */
	public static function create(array $parameters = array()) {
		// obtain default values
		if (!isset($parameters['packageID'])) $parameters['packageID'] = PACKAGE_ID;
		
		parent::create($parameters);
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
		@$file->chmod(0777);
	}
	
	/**
	 * Renames the file of this template.
	 * 
	 * @param	string		$name
	 * @param	integer		$templateGroupID
	 */
	protected function rename($name, $templateGroupID = 0) {
		// get current path
		$currentPath = $this->getPath();

		// get new path		
		$this->data['templateGroupFolderName'] = '';
		if ($templateGroupID != 0) {
			// get folder name
			$sql = "SELECT	templateGroupFolderName
				FROM	wcf".WCF_N."_template_group
				WHERE	templateGroupID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array($templateGroupID));
			$row = $statement->fetchArray();
			$this->data['templateGroupFolderName'] = $row['templateGroupFolderName'];
		}
		
		// delete compiled templates
		$this->deleteCompiledFiles();
		
		// rename
		$this->data['templateName'] = $name;
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
		$statement->execute(array($this->templateID));
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
	 * Deletes the compiled files of this template.
	 */
	public function deleteCompiledFiles() {
		$matches = glob(WCF_DIR . 'templates/compiled/' . $this->packageID . '_*_' . $this->templateName . '.php');
		if (is_array($matches)) {
			foreach ($matches as $match) @unlink($match);
		}
	}
}
?>
