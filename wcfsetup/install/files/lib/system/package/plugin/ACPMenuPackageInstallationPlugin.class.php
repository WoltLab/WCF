<?php
namespace wcf\system\package\plugin;

/**
 * This PIP installs, updates or deletes acp-menu items.
 *
 * @author 	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package.plugin
 * @category 	Community Framework
 */
class ACPMenuPackageInstallationPlugin extends AbstractMenuPackageInstallationPlugin {
	/**
	 * @see	wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::$className
	 */
	public $className = 'wcf\data\acp\menu\item\ACPMenuItemEditor';
	
	/**
	 * @see	wcf\system\package\plugin\AbstractPackageInstallationPlugin::$tableName
	 */
	public $tableName = 'acp_menu_item';
	
	/**
	 * @see	wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::$tagName
	 */	
	public $tagName = 'acpmenuitem';
}
