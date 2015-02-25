<?php
namespace wcf\data\package\installation\plugin;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of package installation plugins.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.package.installation.plugin
 * @category	Community Framework
 */
class PackageInstallationPluginList extends DatabaseObjectList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\package\installation\plugin\PackageInstallationPlugin';
}
