<?php
namespace wcf\system\database\table;
use wcf\system\WCF;

/**
 * PHP representation of an existing database table or the intended layout of an non-existing or
 * existing database table.
 *
 * @author	Alexander Ebert, Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Database\Table
 * @since	5.2
 */
final class DatabaseTableUtil {
	/**
	 * During the update from 3.1 to 5.2.0, the foreign keys of new database tables were not
	 * logged. This method adds the missing logs for the given tables; instances of `PartialDatabaseTable`,
	 * are ignored, however
	 *
	 * This method should be called with the same database tables array as the update from 3.1 to
	 * 5.2.0.
	 * 
	 * (The version numbers here refer to WoltLab version numbers, plugin version numbers will
	 * be different.)
	 * 
	 * @param	integer			$packageID
	 * @param	DatabaseTable[]		$tables
	 * 
	 * @deprecated	5.2.1 This method is only relevant for updates from 5.2.0 to 5.2.1.
	 */
	public static function addMissingForeignKeys($packageID, array $tables) {
		\wcf\functions\deprecatedMethod(__CLASS__, __FUNCTION__);
		$foreignKeys = [];
		foreach ($tables as $table) {
			if ($table instanceof DatabaseTable && !($table instanceof PartialDatabaseTable)) {
				foreach ($table->getForeignKeys() as $foreignKey) {
					$foreignKeys[] = [
						'foreignKey' => $foreignKey->getName(),
						'tableName' => $table->getName()
					];
				}
			}
		}
		
		if (empty($foreignKeys)) {
			return;
		}
		
		$sql = "INSERT IGNORE INTO	wcf" . WCF_N . "_package_installation_sql_log
						(packageID, sqlTable, sqlIndex, isDone)
			VALUES			(?, ?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		WCF::getDB()->beginTransaction();
		foreach ($foreignKeys as $foreignKey) {
			$statement->execute([
				$packageID,
				$foreignKey['tableName'],
				$foreignKey['foreignKey'],
				1
			]);
		}
		WCF::getDB()->commitTransaction();
	}
	
	/**
	 * Forbid creation of `DatabaseTableUtil` objects.
	 */
	private function __construct() {
		// does nothing
	}
}
