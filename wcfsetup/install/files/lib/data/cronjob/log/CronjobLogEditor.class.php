<?php
namespace wcf\data\cronjob\log;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit cronjob logs.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.language.item
 * @category 	Community Framework
 */
class CronjobLogEditor extends DatabaseObjectEditor {
	/**
	 * @see	DatabaseObjectEditor::$baseClass
	 */
	protected static $baseClass = 'wcf\data\cronjob\log\CronjobLog';
	
	/**
	 * Deletes the cronjob log.
	 */
	public static function clearLogs($packageID = PACKAGE_ID) {
		// delete logs
		$sql = "DELETE FROM	wcf".WCF_N."_cronjobs_log
			WHERE		cronjobID IN (
						SELECT	cronjobID
						FROM	wcf".WCF_N."_cronjobs cronjobs,
							wcf".WCF_N."_package_dependency package_dependency
						WHERE 	cronjobs.packageID = package_dependency.dependency
							AND package_dependency.packageID = ?
					)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($packageID);
	}
}
?>