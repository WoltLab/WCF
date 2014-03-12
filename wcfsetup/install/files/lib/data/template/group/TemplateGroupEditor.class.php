<?php
namespace wcf\data\template\group;
use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\cache\builder\TemplateGroupCacheBuilder;
use wcf\util\DirectoryUtil;

/**
 * Provides functions to edit template groups.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.template.group
 * @category	Community Framework
 */
class TemplateGroupEditor extends DatabaseObjectEditor implements IEditableCachedObject {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\template\group\TemplateGroup';
	
	/**
	 * @see	\wcf\data\DatabaseObjectEditor::update()
	 */
	public function update(array $parameters = array()) {
		parent::update($parameters);
		
		if (isset($parameters['templateGroupFolderName']) && ($parameters['templateGroupFolderName'] != $this->templateGroupFolderName)) {
			@rename(WCF_DIR . 'templates/' . $this->templateGroupFolderName, WCF_DIR . 'templates/' . $parameters['templateGroupFolderName']);
		}
	}
	
	/**
	 * @see	\wcf\data\IEditableObject::deleteAll()
	 */
	public static function deleteAll(array $objectIDs = array()) {
		$list = new TemplateGroupList();
		$list->setObjectIDs($objectIDs);
		$list->readObjects();
		foreach ($list as $object) {
			$editor = new TemplateGroupEditor($object);
			$editor->deleteFolder();
		}
		
		return parent::deleteAll($objectIDs);
	}
	
	/**
	 * Deletes the folder of this template group.
	 */
	public function deleteFolder() {
		if (file_exists(WCF_DIR . 'templates/' . $this->templateGroupFolderName)) {
			DirectoryUtil::getInstance(WCF_DIR . 'templates/' . $this->templateGroupFolderName)->removeAll();
		}
	}
	
	/**
	 * @see	\wcf\data\IEditableCachedObject::resetCache()
	 */
	public static function resetCache() {
		TemplateGroupCacheBuilder::getInstance()->reset();
	}
}
