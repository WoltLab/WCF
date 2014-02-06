<?php
namespace wcf\data\template;
use wcf\data\package\PackageCache;
use wcf\data\DatabaseObjectList;
use wcf\system\application\ApplicationHandler;

/**
 * Represents a list of templates.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.template
 * @category	Community Framework
 */
class TemplateList extends DatabaseObjectList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\template\Template';
	
	/**
	 * Creates a new TemplateList object.
	 */
	public function __construct() {
		parent::__construct();
		
		$this->sqlSelects = 'package.package, template_group.templateGroupFolderName';
		$this->sqlJoins = " LEFT JOIN wcf".WCF_N."_package package ON (package.packageID = template.packageID)";
		$this->sqlJoins .= " LEFT JOIN wcf".WCF_N."_template_group template_group ON (template_group.templateGroupID = template.templateGroupID)";
	}
	
	/**
	 * @see	\wcf\data\DatabaseObjectList::readObjects()
	 */
	public function readObjects() {
		parent::readObjects();
		
		foreach ($this->objects as $template) {
			if ($template->application != 'wcf') {
				$application = ApplicationHandler::getInstance()->getApplication($template->application);
			}
			else {
				$application = ApplicationHandler::getInstance()->getWCF();
			}
			$package = PackageCache::getInstance()->getPackage($application->packageID);
			
			// set directory of the application package the template
			// belongs to
			$template->packageDir = $package->packageDir;
		}
	}
}
