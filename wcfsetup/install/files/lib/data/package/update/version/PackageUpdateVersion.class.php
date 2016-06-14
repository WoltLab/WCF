<?php
namespace wcf\data\package\update\version;
use wcf\data\DatabaseObject;

/**
 * Represents a package update version.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Package\Update\Version
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
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'package_update_version';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'packageUpdateVersionID';
}
