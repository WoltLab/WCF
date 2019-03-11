<?php
namespace wcf\system\package\plugin;
use wcf\system\application\ApplicationHandler;
use wcf\system\exception\SystemException;
use wcf\system\package\PackageArchive;
use wcf\system\package\PackageInstallationSQLParser;
use wcf\system\WCF;

/**
 * Executes the delivered sql file.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Package\Plugin
 */
class SQLPackageInstallationPlugin extends AbstractPackageInstallationPlugin {
	/**
	 * @inheritDoc
	 */
	public $tableName = 'package_installation_sql_log';
	
	/**
	 * @inheritDoc
	 */
	public function install() {
		parent::install();
		
		// extract sql file from archive
		if ($queries = $this->getSQL($this->instruction['value'])) {
			// replace app1_ with app{WCF_N}_ in the table names for all applications
			$queries = ApplicationHandler::insertRealDatabaseTableNames($queries, true);
			
			// check queries
			$parser = new PackageInstallationSQLParser($queries, $this->installation->getPackage(), $this->installation->getAction());
			$conflicts = $parser->test();
			if (!empty($conflicts) && (isset($conflicts['CREATE TABLE']) || isset($conflicts['DROP TABLE']))) {
				$unknownCreateTable = isset($conflicts['CREATE TABLE']) ? $conflicts['CREATE TABLE'] : [];
				$unknownDropTable = isset($conflicts['DROP TABLE']) ? $conflicts['DROP TABLE'] : [];
				
				$errorMessage = "Can't";
				if (!empty($unknownDropTable)) {
					$errorMessage .= " drop unknown table";
					if (count($unknownDropTable) > 1) {
						$errorMessage .= "s";
					}
					$errorMessage .= " '".implode("', '", $unknownDropTable)."'";
				}
				if (!empty($unknownCreateTable)) {
					if (!empty($unknownDropTable)) {
						$errorMessage .= " and can't";
					}
					
					$errorMessage .= " overwrite unknown table";
					if (count($unknownCreateTable) > 1) {
						$errorMessage .= "s";
					}
					$errorMessage .= " '".implode("', '", $unknownCreateTable)."'";
				}
				
				throw new SystemException($errorMessage);
			}
			
			// execute queries
			$parser->execute();
			
			// log changes
			$parser->log();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function uninstall() {
		// get logged sql tables/columns
		$sql = "SELECT		wcf".WCF_N."_package_installation_sql_log.*,
					CASE WHEN sqlIndex <> '' THEN 1 ELSE 0 END AS isIndex,
					CASE WHEN sqlColumn <> '' THEN 1 ELSE 0 END AS isColumn,
					CASE WHEN SUBSTRING(sqlIndex, -3) = '_fk' THEN 1 ELSE 0 END AS isForeignKey
			FROM		wcf".WCF_N."_package_installation_sql_log
			WHERE		packageID = ?
			ORDER BY	isIndex DESC,
					isForeignKey DESC,
					sqlIndex,
					isColumn DESC,
					sqlColumn";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$this->installation->getPackageID()]);
		$entries = $statement->fetchAll(\PDO::FETCH_ASSOC);
		
		// get all tablenames from database
		$existingTableNames = WCF::getDB()->getEditor()->getTableNames();
		
		// delete or alter tables
		foreach ($entries as $entry) {
			// don't alter table if it should be dropped
			if (!empty($entry['sqlColumn'])/* || !empty($entry['sqlIndex'])*/) {
				$isDropped = false;
				foreach ($entries as $entry2) {
					if ($entry['sqlTable'] == $entry2['sqlTable'] && empty($entry2['sqlColumn']) && empty($entry2['sqlIndex'])) {
						$isDropped = true;
					}
				}
				if ($isDropped) continue;
			}
			// drop table
			if (!empty($entry['sqlTable']) && empty($entry['sqlColumn']) && empty($entry['sqlIndex'])) {
				WCF::getDB()->getEditor()->dropTable($entry['sqlTable']);
			}
			// drop column
			else if (in_array($entry['sqlTable'], $existingTableNames) && !empty($entry['sqlColumn'])) {
				WCF::getDB()->getEditor()->dropColumn($entry['sqlTable'], $entry['sqlColumn']);
			}
			// drop index
			else if (in_array($entry['sqlTable'], $existingTableNames) && !empty($entry['sqlIndex'])) {
				if (substr($entry['sqlIndex'], -3) == '_fk') {
					WCF::getDB()->getEditor()->dropForeignKey($entry['sqlTable'], $entry['sqlIndex']);
				}
				else {
					WCF::getDB()->getEditor()->dropIndex($entry['sqlTable'], $entry['sqlIndex']);
				}
			}
		}
		// delete from log table
		parent::uninstall();
	}
	
	/**
	 * Extracts and returns the sql file.
	 * If the specified sql file was not found, an error message is thrown.
	 * 
	 * @param	string		$filename
	 * @return	string
	 * @throws	SystemException
	 */
	protected function getSQL($filename) {
		// search sql files in package archive
		if (($fileindex = $this->installation->getArchive()->getTar()->getIndexByFilename($filename)) === false) {
			throw new SystemException("SQL file '".$filename."' not found.");
		}
		
		// extract sql file to string
		return $this->installation->getArchive()->getTar()->extractToString($fileindex);
	}
	
	/**
	 * @inheritDoc
	 */
	public static function getDefaultFilename() {
		return 'install.sql';
	}
	
	/**
	 * @inheritDoc
	 */
	public static function isValid(PackageArchive $archive, $instruction) {
		if (!$instruction) {
			$instruction = static::getDefaultFilename();
		}
		
		if (preg_match('~\.sql$~', $instruction)) {
			// check if file actually exists
			try {
				if ($archive->getTar()->getIndexByFilename($instruction) === false) {
					return false;
				}
			}
			catch (SystemException $e) {
				return false;
			}
			
			return true;
		}
		
		return false;
	}
}
