<?php
namespace wcf\data\package\update;
use wcf\data\DatabaseObject;

/**
 * Represents a package update.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.package.update
 * @category	Community Framework
 */
class PackageUpdate extends DatabaseObject {
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'package_update';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'packageUpdateID';
	
	/**
	 * Returns the name of the package the update belongs to.
	 * 
	 * @return	string
	 */
	public function getName() {
		return $this->packageName;
	}
}
