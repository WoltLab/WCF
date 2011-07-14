<?php
namespace wcf\data\package\installation\plugin;
use wcf\data\DatabaseObject;
use wcf\system\WCF;

/**
 * Represents a package installation plugin.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.package.installation.plugin
 * @category 	Community Framework
 */
class PackageInstallationPlugin extends DatabaseObject {
	/**
	 * @see	DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'package_installation_plugin';
	
	/**
	 * @see	DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'pluginName';
	
	/**
	 * @see	wcf\data\DatabaseObject::$databaseTableIndexIsIdentity
	 */	
	protected static $databaseTableIndexIsIdentity = false;
}
