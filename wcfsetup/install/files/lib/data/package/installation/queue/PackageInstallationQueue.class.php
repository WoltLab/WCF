<?php
namespace wcf\data\package\installation\queue;
use wcf\data\DatabaseObject;
use wcf\system\WCF;

/**
 * Represents a package installation queue.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.package.installation.queue
 * @category	Community Framework
 */
class PackageInstallationQueue extends DatabaseObject {
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'package_installation_queue';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'queueID';
	
	/**
	 * Returns a new process number for package installation queue.
	 * 
	 * @return	integer
	 */
	public static function getNewProcessNo() {
		$sql = "SELECT	MAX(processNo) AS processNo
			FROM	wcf".WCF_N."_package_installation_queue";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		$row = $statement->fetchArray();
		return intval($row['processNo']) + 1;
	}
}
