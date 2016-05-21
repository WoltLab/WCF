<?php
namespace wcf\data\package\update\version;
use wcf\data\DatabaseObject;

/**
 * Represents a package update version.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.package.update.version
 * @category	Community Framework
 *
 * @property-read	integer		$packageUpdateVersionID
 * @property-read	integer		$packageUpdateID
 * @property-read	string		$packageVersion
 * @property-read	integer		$packageDate
 * @property-read	string		$filename
 * @property-read	string		$license
 * @property-read	string		$licenseURL
 * @property-read	integer		$isAccessible
 * @property-read	integer		$isCritical
 */
class PackageUpdateVersion extends DatabaseObject {
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'package_update_version';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'packageUpdateVersionID';
}
