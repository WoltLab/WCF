<?php
namespace wcf\data\template\group;
use wcf\data\DatabaseObjectEditor;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\DirectoryUtil;

/**
 * TemplateGroupEditor provides functions to create, edit or delete template group. 
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.template.group
 * @category 	Community Framework
 */
class TemplateGroupEditor extends DatabaseObjectEditor {
	/**
	 * @see	wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\template\group\TemplateGroup';
	
	/**
	 * @see	wcf\data\DatabaseObjectEditor::update()
	 */
	public function update(array $parameters = array()) {
		parent::update($parameters);
		
		if (isset($parameters['templateGroupFolderName']) && ($parameters['templateGroupFolderName'] != $this->templateGroupFolderName)) {
			$this->renameFolders($parameters['templateGroupFolderName']);
		}
	}
	
	/**
	 * Renames the folders of this template group.
	 * 
	 * @param	string		$newFolderName
	 */
	public function renameFolders($newFolderName) {
		// default template dir
		$folders = array(WCF_DIR . 'templates/' . $this->templateGroupFolderName => WCF_DIR . 'templates/' . $newFolderName);
		
		// get package dirs
		$sql = "SELECT	packageDir
			FROM	wcf".WCF_N."_package
			WHERE	packageDir <> ''";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		while ($row = $statement->fetchArray()) {
			$packageDir = FileUtil::getRealPath(WCF_DIR . $row['packageDir']);
			$folders[$packageDir . 'templates/' . $this->templateGroupFolderName] = $packageDir . 'templates/' . $newFolderName;
		}
		
		// rename folders
		foreach ($folders as $oldName => $newName) {
			if (file_exists($oldName)) {
				@rename($oldName, $newName);
			}
		}
	}
	
	/**
	 * Deletes this template group.
	 */
	public function delete() {
		// update children
		$sql = "UPDATE	wcf".WCF_N."_template_group
			SET	parentTemplateGroupID = ?
			WHERE	parentTemplateGroupID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->parentTemplateGroupID, $this->templateGroupID));
		
		parent::delete();
		
		$this->deleteFolders();
	}
	
	/**
	 * Deletes the folders of this template group.
	 */
	public function deleteFolders() {
		// default template dir
		$folders = array(WCF_DIR . 'templates/' . $this->templateGroupFolderName);
		
		// get package dirs
		$sql = "SELECT	packageDir
			FROM	wcf".WCF_N."_package
			WHERE	packageDir <> ''";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		while ($row = $statement->fetchArray()) {
			$packageDir = FileUtil::getRealPath(WCF_DIR . $row['packageDir']);
			DirectoryUtil::getInstance($packageDir . 'templates/' . $this->templateGroupFolderName)->deleteAll();
		}
	}
}
