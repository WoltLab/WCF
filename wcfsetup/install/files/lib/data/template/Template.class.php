<?php
namespace wcf\data\template;
use wcf\data\package\PackageCache;
use wcf\data\DatabaseObject;
use wcf\system\application\ApplicationHandler;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * Represents a template.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.template
 * @category	Community Framework
 *
 * @property-read	integer		$templateID
 * @property-read	integer		$packageID
 * @property-read	string		$templateName
 * @property-read	string		$application
 * @property-read	integer|null	$templateGroupID
 * @property-read	integer		$lastModificationTime
 */
class Template extends DatabaseObject {
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'template';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'templateID';
	
	/**
	 * @see	\wcf\data\DatabaseObject::__construct()
	 */
	public function __construct($id, $row = null, DatabaseObject $object = null) {
		if ($id !== null) {
			$sql = "SELECT		template.*, template_group.templateGroupFolderName,
						package.package
				FROM		wcf".WCF_N."_template template
				LEFT JOIN	wcf".WCF_N."_template_group template_group
				ON		(template_group.templateGroupID = template.templateGroupID)
				LEFT JOIN	wcf".WCF_N."_package package
				ON		(package.packageID = template.packageID)
				WHERE		template.templateID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$id]);
			$row = $statement->fetchArray();
			
			if ($row !== false) {
				// get relative directory of the template the application
				// belongs to
				if ($row['application'] != 'wcf') {
					$application = ApplicationHandler::getInstance()->getApplication($row['application']);
				}
				else {
					$application = ApplicationHandler::getInstance()->getWCF();
				}
				$row['packageDir'] = PackageCache::getInstance()->getPackage($application->packageID)->packageDir;
			}
			else {
				$row = [];
			}
		}
		else if ($object !== null) {
			$row = $object->data;
		}
		
		$this->handleData($row);
	}
	
	/**
	 * Returns the path to this template.
	 * 
	 * @return	string
	 */
	public function getPath() {
		$path = FileUtil::getRealPath(WCF_DIR . $this->packageDir) . 'templates/' . $this->templateGroupFolderName . $this->templateName . '.tpl';
		return $path;
	}
	
	/**
	 * Returns the source of this template.
	 * 
	 * @return	string
	 */
	public function getSource() {
		return @file_get_contents($this->getPath());
	}
}
