<?php

namespace wcf\system\package;

use wcf\data\package\Package;
use wcf\system\database\util\SQLParser;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

/**
 * Extends SQLParser by testing and logging functions.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class PackageInstallationSQLParser extends SQLParser
{
    /**
     * package object
     * @var Package
     */
    protected $package;

    /**
     * activates the testing mode
     * @var bool
     */
    protected $test = false;

    /**
     * installation type
     * @var string
     */
    protected $action = 'install';

    /**
     * list of existing database tables
     * @var array
     */
    protected $existingTables = [];

    /**
     * list of logged tables
     * @var array
     */
    protected $knownTables = [];

    /**
     * list of conflicted database tables
     * @var array
     */
    protected $conflicts = [];

    /**
     * list of created/deleted tables
     * @var array
     */
    protected $tableLog = [];

    /**
     * list of created/deleted columns
     * @var array
     */
    protected $columnLog = [];

    /**
     * list of created/deleted indices
     * @var array
     */
    protected $indexLog = [];

    /**
     * Creates a new PackageInstallationSQLParser object.
     *
     * @param string $queries
     * @param Package $package
     * @param string $action
     */
    public function __construct($queries, Package $package, $action = 'install')
    {
        $this->package = $package;
        $this->action = $action;

        parent::__construct($queries);
    }

    /**
     * Performs a test of the given queries.
     *
     * @return  array       conflicts
     */
    public function test()
    {
        $this->conflicts = [];

        // get all existing tables from database
        $this->existingTables = WCF::getDB()->getEditor()->getTableNames();

        // get logged tables
        $this->getKnownTables();

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
    public function log()
    {
        // tables
        foreach ($this->tableLog as $logEntry) {
            $sql = "DELETE FROM wcf1_package_installation_sql_log
                    WHERE       sqlTable = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$logEntry['tableName']]);

            if ($logEntry['action'] == 'insert') {
                $sql = "INSERT INTO wcf1_package_installation_sql_log
                                    (packageID, sqlTable)
                        VALUES      (?, ?)";
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute([
                    $logEntry['packageID'],
                    $logEntry['tableName'],
                ]);
            }
        }

        // columns
        if (!empty($this->columnLog)) {
            $sql = "DELETE FROM wcf1_package_installation_sql_log
                    WHERE       sqlTable = ?
                            AND sqlColumn = ?";
            $deleteStatement = WCF::getDB()->prepare($sql);

            $sql = "INSERT INTO wcf1_package_installation_sql_log
                                (packageID, sqlTable, sqlColumn)
                    VALUES      (?, ?, ?)";
            $insertStatement = WCF::getDB()->prepare($sql);

            foreach ($this->columnLog as $logEntry) {
                $deleteStatement->execute([
                    $logEntry['tableName'],
                    $logEntry['columnName'],
                ]);

                if ($logEntry['action'] == 'insert') {
                    $insertStatement->execute([
                        $logEntry['packageID'],
                        $logEntry['tableName'],
                        $logEntry['columnName'],
                    ]);
                }
            }
        }

        // indices
        if (!empty($this->indexLog)) {
            $sql = "DELETE FROM wcf1_package_installation_sql_log
                    WHERE       sqlTable = ?
                            AND sqlIndex = ?";
            $deleteStatement = WCF::getDB()->prepare($sql);

            $sql = "INSERT INTO wcf1_package_installation_sql_log
                                (packageID, sqlTable, sqlIndex)
                    VALUES      (?, ?, ?)";
            $insertStatement = WCF::getDB()->prepare($sql);

            foreach ($this->indexLog as $logEntry) {
                $deleteStatement->execute([
                    $logEntry['tableName'],
                    $logEntry['indexName'],
                ]);

                if ($logEntry['action'] == 'insert') {
                    $insertStatement->execute([
                        $logEntry['packageID'],
                        $logEntry['tableName'],
                        $logEntry['indexName'],
                    ]);
                }
            }
        }
    }

    /**
     * Fetches known sql tables and their owners from installation log.
     */
    protected function getKnownTables()
    {
        $sql = "SELECT  packageID, sqlTable
                FROM    wcf1_package_installation_sql_log
                WHERE   sqlColumn = ''
                    AND sqlIndex = ''";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();
        $this->knownTables = $statement->fetchMap('sqlTable', 'packageID');
    }

    /**
     * Returns the owner of a specific database table column.
     *
     * @param string $tableName
     * @param string $columnName
     * @return  int|null     package id
     */
    protected function getColumnOwnerID($tableName, $columnName)
    {
        $sql = "SELECT  packageID
                FROM    wcf1_package_installation_sql_log
                WHERE   sqlTable = ?
                    AND sqlColumn = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $tableName,
            $columnName,
        ]);
        $row = $statement->fetchArray();
        if (!empty($row['packageID'])) {
            return $row['packageID'];
        } elseif (isset($this->knownTables[$tableName])) {
            return $this->knownTables[$tableName];
        } else {
            return null;
        }
    }

    /**
     * Returns the owner of a specific database index.
     *
     * @param string $tableName
     * @param string $indexName
     * @return  int     package id
     */
    protected function getIndexOwnerID($tableName, $indexName)
    {
        $sql = "SELECT  packageID
                FROM    wcf1_package_installation_sql_log
                WHERE   sqlTable = ?
                    AND sqlIndex = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $tableName,
            $indexName,
        ]);
        $row = $statement->fetchArray();
        if (!empty($row['packageID'])) {
            return $row['packageID'];
        } elseif (isset($this->knownTables[$tableName])) {
            return $this->knownTables[$tableName];
        } else {
            return;
        }
    }

    /**
     * @inheritDoc
     */
    protected function executeCreateTableStatement($tableName, $columns, $indices = [])
    {
        if ($this->test) {
            if (\in_array($tableName, $this->existingTables)) {
                if (
                    isset($this->knownTables[$tableName])
                    && $this->knownTables[$tableName] != $this->package->packageID
                ) {
                    throw new SystemException("Cannot recreate table '" . $tableName . "'. A package can only overwrite own tables.");
                } else {
                    if (!isset($this->conflicts['CREATE TABLE'])) {
                        $this->conflicts['CREATE TABLE'] = [];
                    }
                    $this->conflicts['CREATE TABLE'][] = $tableName;
                }
            }
        } else {
            // log
            $this->tableLog[] = [
                'tableName' => $tableName,
                'packageID' => $this->package->packageID,
                'action' => 'insert',
            ];

            // execute
            parent::executeCreateTableStatement($tableName, $columns, $indices);
        }
    }

    /**
     * @inheritDoc
     */
    protected function executeAddColumnStatement($tableName, $columnName, $columnData)
    {
        if ($this->test) {
            if (!isset($this->knownTables[$tableName])) {
                throw new SystemException("Cannot add column '" . $columnName . "' to table '" . $tableName . "'.");
            }
        } else {
            // log
            $this->columnLog[] = [
                'tableName' => $tableName,
                'columnName' => $columnName,
                'packageID' => $this->package->packageID,
                'action' => 'insert',
            ];

            // execute
            parent::executeAddColumnStatement($tableName, $columnName, $columnData);
        }
    }

    /**
     * @inheritDoc
     */
    protected function executeAlterColumnStatement($tableName, $oldColumnName, $newColumnName, $newColumnData)
    {
        if ($this->test) {
            if ($ownerPackageID = $this->getColumnOwnerID($tableName, $oldColumnName)) {
                if ($ownerPackageID != $this->package->packageID) {
                    throw new SystemException("Cannot alter column '" . $oldColumnName . "'. A package can only change own columns.");
                }
            }
        } else {
            // log
            if ($oldColumnName != $newColumnName) {
                $this->columnLog[] = [
                    'tableName' => $tableName,
                    'columnName' => $oldColumnName,
                    'packageID' => $this->package->packageID,
                    'action' => 'delete',
                ];
                $this->columnLog[] = [
                    'tableName' => $tableName,
                    'columnName' => $newColumnName,
                    'packageID' => $this->package->packageID,
                    'action' => 'insert',
                ];
            }

            // execute
            parent::executeAlterColumnStatement($tableName, $oldColumnName, $newColumnName, $newColumnData);
        }
    }

    /**
     * @inheritDoc
     */
    protected function executeAddIndexStatement($tableName, $indexName, $indexData)
    {
        if (!$this->test) {
            // log
            $this->indexLog[] = [
                'tableName' => $tableName,
                'indexName' => $indexName,
                'packageID' => $this->package->packageID,
                'action' => 'insert',
            ];

            // execute
            parent::executeAddIndexStatement($tableName, $indexName, $indexData);
        }
    }

    /**
     * @inheritDoc
     */
    protected function executeAddForeignKeyStatement($tableName, $indexName, $indexData)
    {
        if (!$this->test) {
            // log
            $this->indexLog[] = [
                'tableName' => $tableName,
                'indexName' => $indexName,
                'packageID' => $this->package->packageID,
                'action' => 'insert',
            ];

            // execute
            parent::executeAddForeignKeyStatement($tableName, $indexName, $indexData);
        }
    }

    /**
     * @inheritDoc
     */
    protected function executeDropColumnStatement($tableName, $columnName)
    {
        if ($this->test) {
            if ($ownerPackageID = $this->getColumnOwnerID($tableName, $columnName)) {
                if ($ownerPackageID != $this->package->packageID) {
                    throw new SystemException("Cannot drop column '" . $columnName . "'. A package can only drop own columns.");
                }
            }
        } else {
            // log
            $this->columnLog[] = [
                'tableName' => $tableName,
                'columnName' => $columnName,
                'packageID' => $this->package->packageID,
                'action' => 'delete',
            ];

            // execute
            parent::executeDropColumnStatement($tableName, $columnName);
        }
    }

    /**
     * @inheritDoc
     */
    protected function executeDropIndexStatement($tableName, $indexName)
    {
        if ($this->test) {
            if ($ownerPackageID = $this->getIndexOwnerID($tableName, $indexName)) {
                if ($ownerPackageID != $this->package->packageID) {
                    throw new SystemException("Cannot drop index '" . $indexName . "'. A package can only drop own indices.");
                }
            }
        } else {
            // log
            $this->indexLog[] = [
                'tableName' => $tableName,
                'indexName' => $indexName,
                'packageID' => $this->package->packageID,
                'action' => 'delete',
            ];

            // execute
            parent::executeDropIndexStatement($tableName, $indexName);
        }
    }

    /**
     * @inheritDoc
     */
    protected function executeDropPrimaryKeyStatement($tableName)
    {
        if ($this->test) {
            if ($ownerPackageID = $this->getIndexOwnerID($tableName, '')) {
                if ($ownerPackageID != $this->package->packageID) {
                    throw new SystemException("Cannot drop primary key from '" . $tableName . "'. A package can only drop own indices.");
                }
            }
        } else {
//          // log
//          $this->indexLog[] = array('tableName' => $tableName, 'indexName' => '', 'packageID' => $this->package->packageID, 'action' => 'delete');

            // execute
            parent::executeDropPrimaryKeyStatement($tableName);
        }
    }

    /**
     * @inheritDoc
     */
    protected function executeDropForeignKeyStatement($tableName, $indexName)
    {
        if ($this->test) {
            if ($ownerPackageID = $this->getIndexOwnerID($tableName, $indexName)) {
                if ($ownerPackageID != $this->package->packageID) {
                    throw new SystemException("Cannot drop index '" . $indexName . "'. A package can only drop own indices.");
                }
            }
        } else {
            // log
            $this->indexLog[] = [
                'tableName' => $tableName,
                'indexName' => $indexName,
                'packageID' => $this->package->packageID,
                'action' => 'delete',
            ];

            // execute
            parent::executeDropForeignKeyStatement($tableName, $indexName);
        }
    }

    /**
     * @inheritDoc
     */
    protected function executeDropTableStatement($tableName)
    {
        if ($this->test) {
            if (\in_array($tableName, $this->existingTables)) {
                if (isset($this->knownTables[$tableName]) && $this->knownTables[$tableName] != $this->package->packageID) {
                    throw new SystemException("Cannot drop table '" . $tableName . "'. A package can only drop own tables.");
                }
            }
        } else {
            // log
            $this->tableLog[] = [
                'tableName' => $tableName,
                'packageID' => $this->package->packageID,
                'action' => 'delete',
            ];

            // execute
            parent::executeDropTableStatement($tableName);
        }
    }

    /**
     * @inheritDoc
     */
    protected function executeStandardStatement($query)
    {
        if (!$this->test) {
            parent::executeStandardStatement($query);
        }
    }
}
