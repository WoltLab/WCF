<?php
namespace wcf\data\package\update;
use wcf\data\DatabaseObject;

/**
 * Represents a package update.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Package\Update
 *
 * @property-read	integer		$packageUpdateID		unique id of the package update
 * @property-read	integer		$packageUpdateServerID		id of the package update server the package update is located on
 * @property-read	string		$package			identifier of the package
 * @property-read	string		$packageName			name of the package
 * @property-read	string		$packageDescription		description of the package
 * @property-read	string		$author				author of the package
 * @property-read	string		$authorURL			external url to the website of the package author
 * @property-read	integer		$isApplication			is `1` if the package update belongs to an application, otherwise `0`
 * @property-read	integer		$pluginStoreFileID		file id for related package on pluginstore.woltlab.com, otherwise `0`
 */
class PackageUpdate extends DatabaseObject {
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
