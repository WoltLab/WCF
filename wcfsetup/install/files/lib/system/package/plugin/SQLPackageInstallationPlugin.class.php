<?php
namespace wcf\system\package\plugin;
use wcf\data\package\Package;
use wcf\data\package\PackageList;
use wcf\system\exception\SystemException;
use wcf\system\form\container\GroupFormElementContainer;
use wcf\system\form\element\LabelFormElement;
use wcf\system\form\FormDocument;
use wcf\system\package\PackageInstallationFormManager;
use wcf\system\package\PackageInstallationSQLParser;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Executes the delivered sql file.
 * 
 * @author	Benjamin Kunz
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package.plugin
 * @category	Community Framework
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
			
			// replace app1_ with app{WCF_N}_ in the table names for
			// all applications
			$packageList = new PackageList();
			$packageList->getConditionBuilder()->add('package.isApplication = ?', array(1));
			$packageList->readObjects();
			foreach ($packageList as $package) {
				$abbreviation = Package::getAbbreviation($package->package);
				
				$queries = StringUtil::replace($abbreviation.'1_', $abbreviation.WCF_N.'_', $queries);
			}
			
			// check queries
			$parser = new PackageInstallationSQLParser($queries, $this->installation->getPackage(), $this->installation->getAction());
			$conflicts = $parser->test();
			if (!empty($conflicts)) {
				if (isset($conflicts['CREATE TABLE']) || isset($conflicts['DROP TABLE'])) {
					if (!PackageInstallationFormManager::findForm($this->installation->queue, 'overwriteDatabaseTables')) {
						$container = new GroupFormElementContainer();
						
						if (isset($conflicts['CREATE TABLE'])) {
							$text = implode('<br />', $conflicts['CREATE TABLE']);
							$label = WCF::getLanguage()->get('wcf.acp.package.error.sql.createTable');
							$description = WCF::getLanguage()->get('wcf.acp.package.error.sql.createTable.description');
							
							$element = new LabelFormElement($container);
							$element->setLabel($label);
							$element->setText($text);
							$element->setDescription($description);
							$container->appendChild($element);
						}
						
						if (isset($conflicts['DROP TABLE'])) {
							$text = implode('<br />', $conflicts['DROP TABLE']);
							$label = WCF::getLanguage()->get('wcf.acp.package.error.sql.dropTable');
							$description = WCF::getLanguage()->get('wcf.acp.package.error.sql.dropTable.description');
							
							$element = new LabelFormElement($container);
							$element->setLabel($label);
							$element->setText($text);
							$element->setDescription($description);
							$container->appendChild($element);
						}
						
						$document = new FormDocument('overwriteDatabaseTables');
						$document->appendContainer($container);
						
						PackageInstallationFormManager::registerForm($this->installation->queue, $document);
						return $document;
					}
					else {
						/*
						 * At this point the user decided to continue the installation (he would called the rollback
						 * otherwise), thus we do not care about the form anymore
						 */
					}
				}
				
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
	 * @see	wcf\system\package\plugin\IPackageInstallationPlugin::uninstall()
	 */
	public function uninstall() {
		// get logged sql tables/columns
		$sql = "SELECT		*
			FROM		wcf".WCF_N."_package_installation_sql_log
			WHERE		packageID = ?
			ORDER BY	sqlIndex DESC, sqlColumn DESC";
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
	 * @return	string
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
