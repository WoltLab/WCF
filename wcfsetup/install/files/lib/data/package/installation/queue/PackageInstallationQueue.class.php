<?php
namespace wcf\data\package\installation\queue;
use wcf\data\DatabaseObject;
use wcf\system\WCF;

/**
 * Represents a package installation queue.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Package\Installation\Queue
 *
 * @property-read	integer		$queueID
 * @property-read	integer		$parentQueueID
 * @property-read	integer		$processNo
 * @property-read	integer		$userID
 * @property-read	string		$package
 * @property-read	string		$packageName
 * @property-read	integer|null	$packageID
 * @property-read	string		$archive
 * @property-read	string		$action
 * @property-read	integer		$done
 * @property-read	integer		$isApplication
 */
class PackageInstallationQueue extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'package_installation_queue';
	
	/**
	 * @inheritDoc
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
