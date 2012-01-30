<?php
namespace wcf\system\package;
use wcf\data\package\Package;
use wcf\system\database\util\SQLParser;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

/**
 * PackageInstallationSQLParser extends SQLParser by testing and logging functions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package
 * @category 	Community Framework
 */
class PackageInstallationSQLParser extends SQLParser {
	/**
	 * package object
	 * @var wcf\data\package\Package
	 */
	protected $package = null;
	
	/**
	 * activates the testing mode
	 * @var	boolean
	 */
	protected $test = false;
	
	/**
	 * installation type
	 * @var	string
	 */
	protected $action = 'install';
	
	/**
	 * list of existing database tables
	 * @var	array
	 */
	protected $existingTables = array();
	
	/**
	 * list of logged tables
	 * @var	array
	 */
	protected $knownTables = array();
	
	/**
	 * list of package ids
	 * @var	array
	 */
	protected $dependentPackageIDs = array();
	
	/**
	 * list of conflicted database tables
	 * @var	array
	 */
	protected $conflicts = array();
	
	/**
	 * list of created/deleted tables
	 * @var	array
	 */
	protected $tableLog = array();
	
	/**
	 * list of created/deleted columns
	 * @var	array
	 */
	protected $columnLog = array();
	
	/**
	 * list of created/deleted indices
	 * @var	array
	 */
	protected $indexLog = array();
	
	/**
	 * Creates a new PackageInstallationSQLParser object.
	 * 
	 * @param	string				$queries
	 * @param	wcf\data\package\Package	$package
	 * @param	string				$action
	 */
	public function __construct($queries, Package $package, $action = 'install') {
		$this->package = $package;
		$this->action = $action;
		parent::__construct($queries);
	}
	
	/**
	 * Performs a test of the given queries.
	 *
	 * @return	array		conflicts
	 */
	public function test() {
		$this->conflicts = array();

		// get all existing tables from database
		$this->existingTables = WCF::getDB()->getEditor()->getTableNames();
		
		// get logged tables
		$this->getKnownTables();
				
		// get package ids of dependencies
		$this->getDependentPackageIDs();
		
		// enable testing mode
		$this->test = true;
		
		// run test
		$this->execute();
		
		// disable testing mode
		$this->test = false;
		
		// return conflicts
		return $this->conflicts;
	}
	
	/**
	 * Logs executed sql queries
	 */
	public function log() {
		// tables
		foreach ($this->tableLog as $logEntry) {
			$sql = "DELETE FROM	wcf".WCF_N."_package_installation_sql_log
				WHERE		sqlTable = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array($logEntry['tableName']));
			
			if ($logEntry['action'] == 'insert') {
				$sql = "INSERT INTO	wcf".WCF_N."_package_installation_sql_log
							(packageID, sqlTable)
					VALUES		(?, ?)";
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute(array(
					$logEntry['packageID'],
					$logEntry['tableName']
				));
			}
		}
		
		// columns
		if (count($this->columnLog)) {
			$sql = "DELETE FROM	wcf".WCF_N."_package_installation_sql_log
				WHERE		sqlTable = ?
						AND sqlColumn = ?";
			$deleteStatement = WCF::getDB()->prepareStatement($sql);
			
			$sql = "INSERT INTO	wcf".WCF_N."_package_installation_sql_log
						(packageID, sqlTable, sqlColumn)
				VALUES		(?, ?, ?)";
			$insertStatement = WCF::getDB()->prepareStatement($sql);
			
			foreach ($this->columnLog as $logEntry) {
				$deleteStatement->execute(array(
					$logEntry['tableName'],
					$logEntry['columnName']
				));
				
				if ($logEntry['action'] == 'insert') {
					$insertStatement->execute(array(
						$logEntry['packageID'],
						$logEntry['tableName'],
						$logEntry['columnName']
					));
				}
			}
		}
		
		// indices
		if (count($this->indexLog)) {
			$sql = "DELETE FROM	wcf".WCF_N."_package_installation_sql_log
				WHERE		sqlTable = ?
						AND sqlIndex = ?";
			$deleteStatement = WCF::getDB()->prepareStatement($sql);
			
			$sql = "INSERT INTO	wcf".WCF_N."_package_installation_sql_log
						(packageID, sqlTable, sqlIndex)
				VALUES		(?, ?, ?)";
			$insertStatement = WCF::getDB()->prepareStatement($sql);
			
			foreach ($this->indexLog as $logEntry) {
				$deleteStatement->execute(array(
					$logEntry['tableName'],
					$logEntry['indexName']
				));
				
				if ($logEntry['action'] == 'insert') {
					$insertStatement->execute(array(
						$logEntry['packageID'],
						$logEntry['tableName'],
						$logEntry['indexName']
					));
				}
			}
		}
	}
	
	/**
	 * Gets known sql tables and their owners from installation log.
	 */
	protected function getKnownTables() {
		$sql = "SELECT	packageID, sqlTable
			FROM	wcf".WCF_N."_package_installation_sql_log
			WHERE	sqlColumn = ''
				AND sqlIndex = ''";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		while ($row = $statement->fetchArray()) {
			$this->knownTables[$row['sqlTable']] = $row['packageID'];
		}
	}
	
	/**
	 * Gets a list of dependent packages.
	 */
	protected function getDependentPackageIDs() {
		$sql = "SELECT		dependency
			FROM		wcf".WCF_N."_package_dependency
			WHERE		packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->package->packageID));
		$packages = array();
		while ($row = $statement->fetchArray()) {
			$this->dependentPackageIDs[] = $row['dependency'];
		}
	}
	
	/**
	 * Returns the owner of a specific database table column.
	 * 
	 * @param	string		$tableName
	 * @param	string		$columnName
	 * @return	integer		package id
	 */
	protected function getColumnOwnerID($tableName, $columnName) {
		$sql = "SELECT	packageID
			FROM	wcf".WCF_N."_package_installation_sql_log
			WHERE	sqlTable = ?
				AND sqlColum = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$tableName,
			$columnName
		));
		$row = $statement->fetchArray();
		if (!empty($row['packageID'])) return $row['packageID'];
		else if (isset($this->knownTables[$tableName])) return $this->knownTables[$tableName];
		else return null;
	}
	
	/**
	 * Returns the owner of a specific database index.
	 * 
	 * @param	string		$tableName
	 * @param	string		$indexName
	 * @return	integer		package id
	 */
	protected function getIndexOwnerID($tableName, $indexName) {
		$sql = "SELECT	packageID
			FROM	wcf".WCF_N."_package_installation_sql_log
			WHERE	sqlTable = ?
				AND sqlIndex = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$tableName,
			$indexName
		));
		$row = $statement->fetchArray();
		if (!empty($row['packageID'])) return $row['packageID'];
		else if (isset($this->knownTables[$tableName])) return $this->knownTables[$tableName];
		else return null;
	}
	
	/**
	 * @see wcf\system\database\util\SQLParser::executeCreateTableStatement()
	 */
	protected function executeCreateTableStatement($tableName, $columns, $indices = array()) {
		if ($this->test) {
			if (in_array($tableName, $this->existingTables)) {
				if (isset($this->knownTables[$tableName])) {
					if ($this->knownTables[$tableName] != $this->package->packageID) {
						throw new SystemException("Can not recreate table '.$tableName.'. A package can only overwrite own tables.");
					}
				}
				else {
					if (!isset($this->conflicts[$tableName])) $this->conflicts[$tableName] = array();
					$this->conflicts[$tableName][] = 'CREATE TABLE';
				}
			}
		}
		else {
			// log
			$this->tableLog[] = array('tableName' => $tableName, 'packageID' => $this->package->packageID, 'action' => 'insert');

			// execute
			parent::executeCreateTableStatement($tableName, $columns, $indices);
		}
	}
	
	/**
	 * @see wcf\system\database\util\SQLParser::executeAddColumnStatement()
	 */
	protected function executeAddColumnStatement($tableName, $columnName, $columnData) {
		if ($this->test) {
			if (isset($this->knownTables[$tableName])) {
				if ($this->knownTables[$tableName] != $this->package->packageID && !in_array($this->knownTables[$tableName], $this->dependentPackageIDs)) {
					throw new SystemException("Can not add column '".$columnName."' to table '.$tableName.'. An installion can only 'ADD' things to tables from the same package environment.");
				}
			}
		}
		else {
			// log
			$this->columnLog[] = array('tableName' => $tableName, 'columnName' => $columnName, 'packageID' => $this->package->packageID, 'action' => 'insert');
			
			// execute
			parent::executeAddColumnStatement($tableName, $columnName, $columnData);
		}
	}
	
	/**
	 * @see wcf\system\database\util\SQLParser::executeAddColumnStatement()
	 */
	protected function executeAlterColumnStatement($tableName, $oldColumnName, $newColumnName, $newColumnData) {
		if ($this->test) {
			if ($ownerPackageID = $this->getColumnOwnerID($tableName, $oldColumnName)) {
				if ($ownerPackageID != $this->package->packageID) {
					throw new SystemException("Can not alter column '.$oldColumnName.'. A package can only change own columns.");
				}
			}
		}
		else {
			// log
			if ($oldColumnName != $newColumnName) {
				$this->columnLog[] = array('tableName' => $tableName, 'columnName' => $oldColumnName, 'packageID' => $this->package->packageID, 'action' => 'delete');
				$this->columnLog[] = array('tableName' => $tableName, 'columnName' => $newColumnName, 'packageID' => $this->package->packageID, 'action' => 'insert');
			}
			
			// execute
			parent::executeAlterColumnStatement($tableName, $oldColumnName, $newColumnName, $newColumnData);
		}
	}
	
	/**
	 * @see wcf\system\database\util\SQLParser::executeAddIndexStatement()
	 */
	protected function executeAddIndexStatement($tableName, $indexName, $indexData) {
		if ($this->test) {
			if (isset($this->knownTables[$tableName])) {
				if ($this->knownTables[$tableName] != $this->package->packageID && !in_array($this->knownTables[$tableName], $this->dependentPackageIDs)) {
					throw new SystemException("Can not add index '".$indexName."' to table '.$tableName.'. An installion can only 'ADD' things to tables from the same package environment.");
				}
			}
		}
		else {
			// log
			$this->indexLog[] = array('tableName' => $tableName, 'indexName' => $indexName, 'packageID' => $this->package->packageID, 'action' => 'insert');
			
			// execute
			parent::executeAddIndexStatement($tableName, $indexName, $indexData);
		}
	}
	
	/**
	 * @see wcf\system\database\util\SQLParser::executeAddForeignKeyStatement()
	 */
	protected function executeAddForeignKeyStatement($tableName, $indexName, $indexData) {
		if ($this->test) {
			if (isset($this->knownTables[$tableName])) {
				if ($this->knownTables[$tableName] != $this->package->packageID && !in_array($this->knownTables[$tableName], $this->dependentPackageIDs)) {
					throw new SystemException("Can not add foreign key '".$indexName."' to table '.$tableName.'. An installion can only 'ADD' things to tables from the same package environment.");
				}
			}
		}
		else {
			// log
			$this->indexLog[] = array('tableName' => $tableName, 'indexName' => $indexName, 'packageID' => $this->package->packageID, 'action' => 'insert');
			
			// execute
			parent::executeAddForeignKeyStatement($tableName, $indexName, $indexData);
		}
	}
	
	/**
	 * @see wcf\system\database\util\SQLParser::executeDropColumnStatement()
	 */
	protected function executeDropColumnStatement($tableName, $columnName) {
		if ($this->test) {
			if ($ownerPackageID = $this->getColumnOwnerID($tableName, $columnName)) {
				if ($ownerPackageID != $this->package->packageID) {
					throw new SystemException("Can not drop column '.$columnName.'. A package can only drop own columns.");
				}
			}
		}
		else {
			// log
			$this->columnLog[] = array('tableName' => $tableName, 'columnName' => $columnName, 'packageID' => $this->package->packageID, 'action' => 'delete');
			
			// execute
			parent::executeDropColumnStatement($tableName, $columnName);
		}
	}
	
	/**
	 * @see wcf\system\database\util\SQLParser::executeDropIndexStatement()
	 */
	protected function executeDropIndexStatement($tableName, $indexName) {
		if ($this->test) {
			if ($ownerPackageID = $this->getIndexOwnerID($tableName, $columnName)) { //TODO: undefined variable
				if ($ownerPackageID != $this->package->packageID) {
					throw new SystemException("Can not drop index '.$indexName.'. A package can only drop own indices.");
				}
			}
		}
		else {
			// log
			$this->indexLog[] = array('tableName' => $tableName, 'indexName' => $indexName, 'packageID' => $this->package->packageID, 'action' => 'delete');
			
			// execute
			parent::executeDropIndexStatement($tableName, $indexName);
		}
	}
	
	/**
	 * @see wcf\system\database\util\SQLParser::executeDropTableStatement()
	 */
	protected function executeDropTableStatement($tableName) {
		if ($this->test) {
			if (in_array($tableName, $this->existingTables)) {
				if (isset($this->knownTables[$tableName])) {
					if ($this->knownTables[$tableName] != $this->package->packageID) {
						throw new SystemException("Can not drop table '.$tableName.'. A package can only drop own tables.");
					}
				}
				else {
					if (!isset($this->conflicts[$tableName])) $this->conflicts[$tableName] = array();
					$this->conflicts[$tableName][] = 'DROP TABLE';
				}
			}
		}
		else {
			// log
			$this->tableLog[] = array('tableName' => $tableName, 'packageID' => $this->package->packageID, 'action' => 'delete');
			
			// execute
			parent::executeDropTableStatement($tableName);
		}
	}
	
	/**
	 * @see wcf\system\database\util\SQLParser::executeStandardStatement()
	 */
	protected function executeStandardStatement($query) {
		if (!$this->test) {
			parent::executeStandardStatement($query);
		}
	}
}
