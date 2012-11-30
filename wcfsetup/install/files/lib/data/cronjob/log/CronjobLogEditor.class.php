<?php
namespace wcf\data\cronjob\log;
use wcf\data\DatabaseObjectEditor;
use wcf\system\WCF;

/**
 * Provides functions to edit cronjob logs.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.language.item
 * @category	Community Framework
 */
class CronjobLogEditor extends DatabaseObjectEditor {
	/**
	 * @see	wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\cronjob\log\CronjobLog';
	
	/**
	 * Deletes the cronjob logs for the package with the given id.
	 * 
	 * @param	integer		$packageID
	 */
	public static function clearLogs($packageID = PACKAGE_ID) {
		// delete logs
		$sql = "DELETE FROM	wcf".WCF_N."_cronjob_log
			WHERE		cronjobID IN (
						SELECT	cronjobID
						FROM	wcf".WCF_N."_cronjob cronjob,
							wcf".WCF_N."_package_dependency package_dependency
						WHERE	cronjob.packageID = package_dependency.dependency
							AND package_dependency.packageID = ?
					)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($packageID));
	}
}
