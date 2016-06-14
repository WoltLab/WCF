<?php
namespace wcf\data\package\update;
use wcf\data\DatabaseObject;

/**
 * Represents a package update.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Package\Update
 *
 * @property-read	integer		$packageUpdateID
 * @property-read	integer		$packageUpdateServerID
 * @property-read	string		$package
 * @property-read	string		$packageName
 * @property-read	string		$packageDescription
 * @property-read	string		$author
 * @property-read	string		$authorURL
 * @property-read	integer		$isApplication
 */
class PackageUpdate extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'package_update';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'packageUpdateID';
	
	/**
	 * package update API version
	 * @var	string
	 */
	const API_VERSION = '2.1';
	
	/**
	 * Returns the name of the package the update belongs to.
	 * 
	 * @return	string
	 */
	public function getName() {
		return $this->packageName;
	}
}
