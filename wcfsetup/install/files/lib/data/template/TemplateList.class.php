<?php
namespace wcf\data\template;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of templates.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.template
 * @category	Community Framework
 */
class TemplateList extends DatabaseObjectList {
	/**
	 * @see	wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\template\Template';
	
	/**
	 * Creates a new TemplateList object.
	 */
	public function __construct() {
		parent::__construct();
		
		$this->sqlSelects = 'package.package, package.packageDir, template_group.templateGroupFolderName';
		$this->sqlJoins = " LEFT JOIN wcf".WCF_N."_package package ON (package.packageID = template.packageID)";
		$this->sqlJoins .= " LEFT JOIN wcf".WCF_N."_template_group template_group ON (template_group.templateGroupID = template.templateGroupID)";
	}
}
