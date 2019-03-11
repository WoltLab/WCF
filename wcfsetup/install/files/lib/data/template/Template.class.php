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
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Template
 *
 * @property-read	integer		$templateID		unique id of the template
 * @property-read	integer		$packageID		id of the package which delivers the template
 * @property-read	string		$templateName		name of the template
 * @property-read	string		$application		abbreviation of the application to which the template belongs
 * @property-read	integer|null	$templateGroupID	id of the template group to which the template belongs or `null` if the template belongs to no template group
 * @property-read	integer		$lastModificationTime	timestamp at which the template has been edited the last time
 */
class Template extends DatabaseObject {
	/**
	 * list of system critical templates
	 * @var string[]
	 */
	protected static $systemCriticalTemplates = ['headIncludeJavaScript', 'wysiwyg', 'wysiwygToolbar'];
	
	/** @noinspection PhpMissingParentConstructorInspection */
	/**
	 * @inheritDoc
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
		/** @noinspection PhpUndefinedFieldInspection */
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
	
	/**
	 * Returns true if current template is considered system critical and
	 * may not be customized at any point.
	 * 
	 * @return      boolean
	 */
	public function canCopy() {
		if (self::isSystemCritical($this->templateName)) {
			// system critical templates cannot be modified, because whatever the
			// gain of a customized version is, the damage potential is much higher
			return false;
		}
		
		return true;
	}
	
	/**
	 * Returns true if current template is considered system critical and
	 * may not be customized at any point.
	 * 
	 * @param       string          $templateName
	 * @return      boolean
	 */
	public static function isSystemCritical($templateName) {
		return in_array($templateName, self::$systemCriticalTemplates);
	}
}
