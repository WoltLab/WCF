<?php
namespace wcf\system\package\plugin;
use wcf\data\package\Package;
use wcf\system\exception\SystemException;
use wcf\system\package\PackageInstallationSQLParser;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * This PIP executes the delivered sql file.
 *
 * @author 	Benjamin Kunz
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package.plugin
 * @category 	Community Framework
 */
class SQLPackageInstallationPlugin extends AbstractPackageInstallationPlugin {
	/**
	 * @see	wcf\system\package\plugin\AbstractPackageInstallationPlugin::$tableName
	 */	
	public $tableName = 'package_installation_sql_log';
	
	/**
	 * @see	wcf\system\package\plugin\IPackageInstallationPlugin::install()
	 */
	public function install() {
		parent::install();

		// extract sql file from archive
		if ($queries = $this->getSQL($this->instruction['value'])) {
			$package = $this->installation->getPackage();
			if ($package->parentPackageID) {
				// package is a plugin; get parent package
				$package = $package->getParentPackage();
			}
			
			if ($package->isApplication == 1) {
				// package is application
				$packageAbbr = Package::getAbbreviation($package->package);
				$tablePrefix = WCF_N.'_'.$package->instanceNo.'_';
				
				// Replace the variable xyz1_1 with $tablePrefix in the table names.
				$queries = StringUtil::replace($packageAbbr.'1_1_', $packageAbbr.$tablePrefix, $queries);
			}
			
			// replace wcf1_  with the actual WCF_N value
			$queries = str_replace("wcf1_", "wcf".WCF_N."_", $queries);
			
			// check queries
			$parser = new PackageInstallationSQLParser($queries, $package, $this->installation->getAction());
			$conflicts = $parser->test();
			if (count($conflicts)) {
				// ask user here
				// search default value in session
				if (!WCF::getSession()->getVar('overrideAndDontAskAgain')) {
					// show page
					if (!empty($_POST['override']) || !empty($_POST['overrideAndDontAskAgain'])) {
						if (!empty($_POST['overrideAndDontAskAgain'])) {
							WCF::getSession()->register('overrideAndDontAskAgain', true);
							WCF::getSession()->update();
						}
					}
					else {
						WCF::getTPL()->assign('conflicts', $conflicts);
						WCF::getTPL()->display('packageInstallationCheckOverrideTables');
						exit;
					}
				}
			}
			
			// execute queries
			$parser->execute();
			
			// log changes
			$parser->log();
		}
	}
	
	/**
	 * Deletes the sql tables or columns which where installed by the package.
	 */
	public function uninstall() {
		// get logged sql tables/columns
		$sql = "SELECT		*
			FROM		wcf".WCF_N."_package_installation_sql_log
			WHERE		packageID = ?
			ORDER BY 	sqlIndex DESC, sqlColumn DESC";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->installation->getPackageID()));
		$entries = array();
		while ($row = $statement->fetchArray()) {
			$entries[] = $row;
		}
		
		// get all tablenames from database
		$existingTableNames = WCF::getDB()->getEditor()->getTablenames();
		
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
	 * @return 	string
	 */
	protected function getSQL($filename) {
		// search sql files in package archive
		if (($fileindex = $this->installation->getArchive()->getTar()->getIndexByFilename($filename)) === false) {
			throw new SystemException("SQL file '".$filename."' not found.");
		}

		// extract sql file to string
		return $this->installation->getArchive()->getTar()->extractToString($fileindex);
 	}
}
