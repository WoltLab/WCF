<?php
namespace wcf\data\template\group;
use wcf\data\package\PackageCache;
use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\application\ApplicationHandler;
use wcf\system\cache\builder\TemplateGroupCacheBuilder;
use wcf\system\WCF;
use wcf\util\DirectoryUtil;

/**
 * Provides functions to edit template groups.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.template.group
 * @category	Community Framework
 * 
 * @method	TemplateGroup	getDecoratedObject()
 * @mixin	TemplateGroup
 */
class TemplateGroupEditor extends DatabaseObjectEditor implements IEditableCachedObject {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = TemplateGroup::class;
	
	/**
	 * @inheritDoc
	 */
	public function update(array $parameters = []) {
		parent::update($parameters);
		
		if (isset($parameters['templateGroupFolderName']) && ($parameters['templateGroupFolderName'] != $this->templateGroupFolderName)) {
			@rename(WCF_DIR . 'templates/' . $this->templateGroupFolderName, WCF_DIR . 'templates/' . $parameters['templateGroupFolderName']);
			
			// check template group folders in other applications
			$sql = "SELECT	DISTINCT application
				FROM	wcf".WCF_N."_template
				WHERE	templateGroupID = ?
					AND application <> ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$this->templateGroupID, 'wcf']);
			while ($row = $statement->fetchArray()) {
				$application = ApplicationHandler::getInstance()->getApplication($row['application']);
				$package = PackageCache::getInstance()->getPackage($application->packageID);
				
				@rename(WCF_DIR . $package->packageDir . 'templates/' . $this->templateGroupFolderName, WCF_DIR . $package->packageDir . 'templates/' . $parameters['templateGroupFolderName']);
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public static function deleteAll(array $objectIDs = []) {
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
		
		// check template group folders in other applications
		$sql = "SELECT	DISTINCT application
			FROM	wcf".WCF_N."_template
			WHERE	templateGroupID = ?
				AND application <> ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$this->templateGroupID, 'wcf']);
		while ($row = $statement->fetchArray()) {
			$application = ApplicationHandler::getInstance()->getApplication($row['application']);
			$package = PackageCache::getInstance()->getPackage($application->packageID);
			
			if (file_exists(WCF_DIR . $package->packageDir . 'templates/' . $this->templateGroupFolderName)) {
				DirectoryUtil::getInstance(WCF_DIR . $package->packageDir . 'templates/' . $this->templateGroupFolderName)->removeAll();
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public static function resetCache() {
		TemplateGroupCacheBuilder::getInstance()->reset();
	}
}
