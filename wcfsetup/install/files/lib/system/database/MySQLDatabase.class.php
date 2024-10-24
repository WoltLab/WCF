<?php

namespace wcf\system\database;

use wcf\system\database\editor\MySQLDatabaseEditor;
use wcf\system\database\exception\DatabaseException as GenericDatabaseException;

/**
 * This is the database implementation for MySQL 5.1 or higher using PDO.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class MySQLDatabase extends Database
{
    /**
     * @inheritDoc
     */
    protected $editorClassName = MySQLDatabaseEditor::class;

    /**
     * @inheritDoc
     */
    public function connect()
    {
        if (!$this->port) {
            $this->port = 3306; // mysql default port
        }

        try {
            $driverOptions = $this->defaultDriverOptions;
            $driverOptions[\PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES 'utf8mb4'";
            if (!$this->failsafeTest) {
                $driverOptions[\PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES 'utf8mb4', SESSION sql_mode = 'ANSI,ONLY_FULL_GROUP_BY,STRICT_ALL_TABLES,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'";
            }

            // disable prepared statement emulation since MySQL 5.1.17 is the minimum required version
            $driverOptions[\PDO::ATTR_EMULATE_PREPARES] = false;

            // throw PDOException instead of dumb false return values
            $driverOptions[\PDO::ATTR_ERRMODE] = \PDO::ERRMODE_EXCEPTION;

            $dsn = 'mysql:host=' . $this->host . ';port=' . $this->port;
            if (!$this->tryToCreateDatabase) {
                $dsn .= ';dbname=' . $this->database;
            }

            $this->pdo = new \PDO($dsn, $this->user, $this->password, $driverOptions);
            $this->setAttributes();

            if ($this->tryToCreateDatabase) {
                try {
                    $this->pdo->exec("USE " . $this->database);
                } catch (\PDOException $e) {
                    // 1049 = Unknown database
                    if ($this->pdo->errorInfo()[1] == 1049) {
                        $this->pdo->exec("CREATE DATABASE " . $this->database);
                        $this->pdo->exec("USE " . $this->database);
                    } else {
                        throw $e;
                    }
                }
            }
        } catch (\PDOException $e) {
            throw new GenericDatabaseException("Connecting to MySQL server '" . $this->host . "' failed", $e);
        }
    }

    /**
     * @inheritDoc
     */
    public static function isSupported()
    {
        return \extension_loaded('PDO') && \extension_loaded('pdo_mysql');
    }

    /**
     * @inheritDoc
     */
    protected function setAttributes()
    {
        parent::setAttributes();
        $this->pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
    }

    /**
     * @inheritDoc
     */
    public function getVersion()
    {
        try {
            $statement = $this->prepare('SELECT VERSION()');
            $statement->execute();

            return $statement->fetchSingleColumn();
        } catch (\PDOException $e) {
        }

        return 'unknown';
    }
}
