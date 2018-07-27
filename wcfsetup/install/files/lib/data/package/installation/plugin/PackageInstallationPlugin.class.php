<?php
namespace wcf\data\package\installation\plugin;
use wcf\data\DatabaseObject;

/**
 * Represents a package installation plugin.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Package\Installation\Plugin
 *
 * @property-read	string		$pluginName	unique name and textual identifier of the package installation plugin
 * @property-read	integer|null	$packageID	id of the package the which delivers the package installation plugin
 * @property-read	integer		$priority	priority in which the package installation plugins are installed, `1` for Core package installation plugins (executed first) and `0` for other package installation plugins
 * @property-read	string		$className	name of the PHP class implementing `wcf\system\package\plugin\IPackageInstallationPlugin` handling installing and uninstalling handled data
 */
class PackageInstallationPlugin extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'pluginName';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexIsIdentity = false;
}
