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
 * @deprecated 6.3 Use the PHP-DDL instead.
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
     * @var string[]
     */
    protected $existingTables = [];

    /**
     * list of logged tables
     * @var array<string, int>
     */
    protected $knownTables = [];

    /**
     * list of conflicted database tables
     * @var array{'CREATE TABLE'?: list<string>}
     */
    protected $conflicts = [];

    /**
     * list of created/deleted tables
     * @var list<array{tableName: string, packageID: int, action: 'delete'|'insert'}>
     */
    protected $tableLog = [];

    /**
     * list of created/deleted columns
     * @var list<array{tableName: string, columnName: string, packageID: int, action: 'delete'|'insert'}>
     */
    protected $columnLog = [];

    /**
     * list of created/deleted indices
     * @var list<array{tableName: string, indexName: string, packageID: int, action: 'delete'|'insert'}>
     */
    protected $indexLog = [];

    public function __construct(string $queries, Package $package, string $action = 'install')
    {
        $this->package = $package;
        $this->action = $action;

        parent::__construct($queries);
    }

    /**
     * Performs a test of the given queries.
     *
     * @return array{'CREATE TABLE'?: list<string>} conflicts
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
     *
     * @return void
     */
    public function log()
    {
        // tables
        foreach ($this->tableLog as $logEntry) {
            $sql = "DELETE FROM wcf1_package_installation_sql_log
                    WHERE       sqlTable = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$logEntry['tableName']]);

            if ($logEntry['action'] === 'insert') {
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
        if ($this->columnLog !== []) {
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

                if ($logEntry['action'] === 'insert') {
                    $insertStatement->execute([
                        $logEntry['packageID'],
                        $logEntry['tableName'],
                        $logEntry['columnName'],
                    ]);
                }
            }
        }

        // indices
        if ($this->indexLog !== []) {
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

                if ($logEntry['action'] === 'insert') {
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
     *
     * @return void
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
     * @return  int|null     package id
     */
    protected function getColumnOwnerID(string $tableName, string $columnName)
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
     * @return  int     package id
     */
    protected function getIndexOwnerID(string $tableName, string $indexName)
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
        }

        return 0;
    }

    #[\Override]
    protected function executeCreateTableStatement(string $tableName, array $columns, array $indices = [])
    {
        if ($this->test) {
            if (\in_array($tableName, $this->existingTables)) {
                if (
                    isset($this->knownTables[$tableName])
                    && $this->knownTables[$tableName] !== $this->package->packageID
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

    #[\Override]
    protected function executeAddColumnStatement(string $tableName, string $columnName, array $columnData)
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

    #[\Override]
    protected function executeAlterColumnStatement(string $tableName, string $oldColumnName, string $newColumnName, array $newColumnData)
    {
        if ($this->test) {
            if (($ownerPackageID = $this->getColumnOwnerID($tableName, $oldColumnName)) !== null) {
                if ($ownerPackageID !== $this->package->packageID) {
                    throw new SystemException("Cannot alter column '" . $oldColumnName . "'. A package can only change own columns.");
                }
            }
        } else {
            // log
            if ($oldColumnName !== $newColumnName) {
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

    #[\Override]
    protected function executeAddIndexStatement(string $tableName, string $indexName, array $indexData)
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

    #[\Override]
    protected function executeAddForeignKeyStatement(string $tableName, string $indexName, array $indexData)
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

    #[\Override]
    protected function executeDropColumnStatement(string $tableName, string $columnName)
    {
        if ($this->test) {
            if (($ownerPackageID = $this->getColumnOwnerID($tableName, $columnName)) !== null) {
                if ($ownerPackageID !== $this->package->packageID) {
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

    #[\Override]
    protected function executeDropIndexStatement(string $tableName, string $indexName)
    {
        if ($this->test) {
            if (($ownerPackageID = $this->getIndexOwnerID($tableName, $indexName)) !== 0) {
                if ($ownerPackageID !== $this->package->packageID) {
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

    #[\Override]
    protected function executeDropPrimaryKeyStatement(string $tableName)
    {
        if ($this->test) {
            if (($ownerPackageID = $this->getIndexOwnerID($tableName, '')) !== 0) {
                if ($ownerPackageID !== $this->package->packageID) {
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

    #[\Override]
    protected function executeDropForeignKeyStatement(string $tableName, string $indexName)
    {
        if ($this->test) {
            if (($ownerPackageID = $this->getIndexOwnerID($tableName, $indexName)) !== 0) {
                if ($ownerPackageID !== $this->package->packageID) {
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

    #[\Override]
    protected function executeDropTableStatement(string $tableName)
    {
        if ($this->test) {
            if (\in_array($tableName, $this->existingTables)) {
                if (isset($this->knownTables[$tableName]) && $this->knownTables[$tableName] !== $this->package->packageID) {
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

    #[\Override]
    protected function executeStandardStatement(string $query)
    {
        if (!$this->test) {
            parent::executeStandardStatement($query);
        }
    }
}
