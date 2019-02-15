<?php
namespace wcf\data\package\installation\queue;
use wcf\data\DatabaseObject;
use wcf\system\WCF;

/**
 * Represents a package installation queue entry.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Package\Installation\Queue
 *
 * @property-read	integer		$queueID		unique id of the package installation queue entry
 * @property-read	integer		$parentQueueID		id of the package installation queue entry's parent entry or `0` if it has no parent entry
 * @property-read	integer		$processNo		numerical identifier of a group of dependent package installation queue entries, i.e. a parent entry and all of its children
 * @property-read	integer		$userID			id of the user who started the package installation, update or uninstallation
 * @property-read	string		$package		identifier of the relevant package
 * @property-read	string		$packageName		name of the relevant package
 * @property-read	integer|null	$packageID		id of relevant package
 * @property-read	string		$archive		location of the package file for `$action = install` or `$action = update`, otherwise empty
 * @property-read	string		$action			action the package installation queue entry belongs to (`install`, `update`, `uninstall`)
 * @property-read	integer		$done			is `1` if the package installation queue entry has been completed, otherwise `0`
 * @property-read	integer		$isApplication		is `1` if the package installation queue entry belongs to an application, otherwise `0`
 */
class PackageInstallationQueue extends DatabaseObject {
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
