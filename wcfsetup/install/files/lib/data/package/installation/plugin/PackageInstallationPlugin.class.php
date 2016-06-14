<?php
namespace wcf\data\package\installation\plugin;
use wcf\data\DatabaseObject;

/**
 * Represents a package installation plugin.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Package\Installation\Plugin
 *
 * @property-read	string		$pluginName
 * @property-read	integer|null	$packageID
 * @property-read	integer		$priority
 * @property-read	string		$className
 */
class PackageInstallationPlugin extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'package_installation_plugin';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'pluginName';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexIsIdentity = false;
}
