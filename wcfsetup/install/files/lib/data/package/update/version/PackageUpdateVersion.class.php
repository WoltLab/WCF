<?php
namespace wcf\data\package\update\version;
use wcf\data\DatabaseObject;

/**
 * Represents a package update version.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Package\Update\Version
 *
 * @property-read	integer		$packageUpdateVersionID		unique id of the package update version
 * @property-read	integer		$packageUpdateID		id of the package update the package update version belongs to
 * @property-read	string		$packageVersion			version number of the package update version
 * @property-read	integer		$packageDate			date of the package update version
 * @property-read	string		$filename			location of the package update version file or empty if no file is given
 * @property-read	string		$license			name of the license of the package update version or empty if no license is given
 * @property-read	string		$licenseURL			link to the license of the package update version or empty if no license or license link is given
 * @property-read	integer		$isAccessible			is `1` if the package update version is accessible and thus can be installed, otherwise `0`
 */
class PackageUpdateVersion extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'packageUpdateVersionID';
}
