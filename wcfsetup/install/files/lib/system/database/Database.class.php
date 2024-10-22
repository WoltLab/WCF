<?php

namespace wcf\system\database;

use wcf\system\application\ApplicationHandler;
use wcf\system\benchmark\Benchmark;
use wcf\system\database\editor\DatabaseEditor;
use wcf\system\database\exception\DatabaseException as GenericDatabaseException;
use wcf\system\database\exception\DatabaseQueryException;
use wcf\system\database\exception\DatabaseTransactionException;
use wcf\system\database\statement\DebugPreparedStatement;
use wcf\system\database\statement\PreparedStatement;
use wcf\system\WCF;

/**
 * Abstract implementation of a database access class using PDO.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
abstract class Database
{
    /**
     * name of the class used for prepared statements
     * @var string
     */
    protected $preparedStatementClassName = PreparedStatement::class;

    /**
     * name of the database editor class
     * @var string
     */
    protected $editorClassName = DatabaseEditor::class;

    /**
     * sql server hostname
     * @var string
     */
    protected $host = '';

    /**
     * sql server post
     * @var int
     */
    protected $port = 0;

    /**
     * sql server login name
     * @var string
     */
    protected $user = '';

    /**
     * sql server login password
     * @var string
     */
    protected $password = '';

    /**
     * database name
     * @var string
     */
    protected $database = '';

    /**
     * enables failsafe connection
     * @var bool
     */
    protected $failsafeTest = false;

    /**
     * number of executed queries
     * @var int
     */
    protected $queryCount = 0;

    /**
     * database editor object
     * @var DatabaseEditor
     */
    protected $editor;

    /**
     * pdo object
     * @var \PDO
     */
    protected $pdo;

    /**
     * amount of active transactions
     * @var int
     */
    protected $activeTransactions = 0;

    /**
     * attempts to create the database after the connection has been established
     * @var bool
     */
    protected $tryToCreateDatabase = false;

    /**
     * default driver options passed to the PDO constructor
     * @var array
     */
    protected $defaultDriverOptions = [];

    /**
     * Creates a Database Object.
     *
     * @param string $host SQL database server host address
     * @param string $user SQL database server username
     * @param string $password SQL database server password
     * @param string $database SQL database server database name
     * @param int $port SQL database server port
     * @param bool $failsafeTest
     * @param bool $tryToCreateDatabase
     * @param array $defaultDriverOptions
     */
    public function __construct(
        $host,
        $user,
        #[\SensitiveParameter]
        $password,
        $database,
        $port,
        $failsafeTest = false,
        $tryToCreateDatabase = false,
        $defaultDriverOptions = []
    ) {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->password = $password;
        $this->database = $database;
        $this->failsafeTest = $failsafeTest;
        $this->tryToCreateDatabase = $tryToCreateDatabase;
        $this->defaultDriverOptions = $defaultDriverOptions;

        // connect database
        $this->connect();
    }

    public function enableDebugMode()
    {
        $this->preparedStatementClassName = DebugPreparedStatement::class;
    }

    /**
     * Connects to database server.
     */
    abstract public function connect();

    /**
     * Returns ID from last insert.
     *
     * @param string $table
     * @param string $field
     * @return  string|false
     * @throws  DatabaseException
     */
    public function getInsertID($table, $field)
    {
        try {
            return $this->pdo->lastInsertId();
        } catch (\PDOException $e) {
            throw new GenericDatabaseException("Cannot fetch last insert id", $e);
        }
    }

    /**
     * Initiates a transaction.
     *
     * @return  bool        true on success
     * @throws  DatabaseTransactionException
     */
    public function beginTransaction()
    {
        try {
            if ($this->activeTransactions === 0) {
                if (WCF::benchmarkIsEnabled()) {
                    Benchmark::getInstance()->start("BEGIN", Benchmark::TYPE_SQL_QUERY);
                }
                $result = $this->pdo->beginTransaction();
            } else {
                if (WCF::benchmarkIsEnabled()) {
                    Benchmark::getInstance()->start(
                        "SAVEPOINT level" . $this->activeTransactions,
                        Benchmark::TYPE_SQL_QUERY
                    );
                }
                $result = $this->pdo->exec("SAVEPOINT level" . $this->activeTransactions) !== false;
            }
            if (WCF::benchmarkIsEnabled()) {
                Benchmark::getInstance()->stop();
            }

            $this->activeTransactions++;

            return $result;
        } catch (\PDOException $e) {
            throw new DatabaseTransactionException("Could not begin transaction", $e);
        }
    }

    /**
     * Commits a transaction and returns true if the transaction was successful.
     *
     * @return  bool
     * @throws  DatabaseTransactionException
     */
    public function commitTransaction()
    {
        if ($this->activeTransactions === 0) {
            return false;
        }

        try {
            $this->activeTransactions--;

            if ($this->activeTransactions === 0) {
                if (WCF::benchmarkIsEnabled()) {
                    Benchmark::getInstance()->start("COMMIT", Benchmark::TYPE_SQL_QUERY);
                }
                $result = $this->pdo->commit();
            } else {
                if (WCF::benchmarkIsEnabled()) {
                    Benchmark::getInstance()->start(
                        "RELEASE SAVEPOINT level" . $this->activeTransactions,
                        Benchmark::TYPE_SQL_QUERY
                    );
                }
                $result = $this->pdo->exec("RELEASE SAVEPOINT level" . $this->activeTransactions) !== false;
            }

            if (WCF::benchmarkIsEnabled()) {
                Benchmark::getInstance()->stop();
            }

            return $result;
        } catch (\PDOException $e) {
            throw new DatabaseTransactionException("Could not commit transaction", $e);
        }
    }

    /**
     * Rolls back a transaction and returns true if the rollback was successful.
     *
     * @return  bool
     * @throws  DatabaseTransactionException
     */
    public function rollBackTransaction()
    {
        if ($this->activeTransactions === 0) {
            return false;
        }

        try {
            $this->activeTransactions--;
            if ($this->activeTransactions === 0) {
                if (WCF::benchmarkIsEnabled()) {
                    Benchmark::getInstance()->start("ROLLBACK", Benchmark::TYPE_SQL_QUERY);
                }
                $result = $this->pdo->rollBack();
            } else {
                if (WCF::benchmarkIsEnabled()) {
                    Benchmark::getInstance()->start(
                        "ROLLBACK TO SAVEPOINT level" . $this->activeTransactions,
                        Benchmark::TYPE_SQL_QUERY
                    );
                }
                $result = $this->pdo->exec("ROLLBACK TO SAVEPOINT level" . $this->activeTransactions) !== false;
            }

            if (WCF::benchmarkIsEnabled()) {
                Benchmark::getInstance()->stop();
            }

            return $result;
        } catch (\PDOException $e) {
            throw new DatabaseTransactionException("Could not roll back transaction", $e);
        }
    }

    /**
     * Prepares a statement for execution and returns a statement object.
     *
     * @throws DatabaseQueryException
     */
    public function prepareUnmanaged(string $statement, int $limit = 0, int $offset = 0): PreparedStatement
    {
        $statement = $this->handleLimitParameter($statement, $limit, $offset);

        try {
            // Append routing information of the current request as a comment.
            // This allows the system administrator to find offending requests
            // in MySQL's slow query log and / or MySQL's process list.
            // Note: This is meant to be run unconditionally in production to be
            //       useful. Thus the code to retrieve the request information
            //       must be absolutely lightweight.
            static $requestInformation = null;
            if ($requestInformation === null) {
                $requestInformation = '';
                if (
                    \defined('ENABLE_PRODUCTION_DEBUG_MODE')
                    && ENABLE_PRODUCTION_DEBUG_MODE
                    && isset($_SERVER['REQUEST_URI'])
                ) {
                    $requestInformation = $_SERVER['REQUEST_URI'];
                    if ($requestId = \wcf\getRequestId()) {
                        $requestInformation = \substr($requestInformation, 0, 70);
                        $requestInformation .= ' (' . $requestId . ')';
                    }
                    if (
                        isset($_REQUEST['className'])
                        && isset($_REQUEST['actionName'])
                        && \is_string($_REQUEST['className'])
                        && \is_string($_REQUEST['actionName'])
                    ) {
                        $requestInformation = \substr($requestInformation, 0, 90);
                        $requestInformation .= ' (' . $_REQUEST['className'] . ':' . $_REQUEST['actionName'] . ')';
                    }
                    $requestInformation = \substr($requestInformation, 0, 180);
                }
            }

            $pdoStatement = $this->pdo->prepare(
                $statement . ($requestInformation ? " -- " . $this->pdo->quote($requestInformation) : '')
            );

            return new $this->preparedStatementClassName($this, $pdoStatement, $statement);
        } catch (\PDOException $e) {
            throw new DatabaseQueryException("Could not prepare statement '" . $statement . "'", $e);
        }
    }

    /**
     * Prepares a statement for execution and returns a statement object.
     *
     * @param string $statement
     * @param int    $limit
     * @param int    $offset
     *
     * @return  PreparedStatement
     * @throws  DatabaseQueryException
     *
     * @deprecated 6.2 Use `prepareUnmanaged()` or `prepare()` instead.
     */
    public function prepareStatement($statement, $limit = 0, $offset = 0)
    {
        return $this->prepareUnmanaged($statement, $limit, $offset);
    }

    /**
     * Prepares a statement for execution and returns a statement object.
     *
     * In contrast to `prepareStatement()`, for all installed apps, `app1_` is replaced with
     * `app{WCF_N}_`.
     *
     * @since   5.4
     */
    public function prepare(string $statement, int $limit = 0, int $offset = 0): PreparedStatement
    {
        $statement = ApplicationHandler::insertRealDatabaseTableNames($statement);

        return $this->prepareUnmanaged($statement, $limit, $offset);
    }

    /**
     * Handles the limit and offset parameter in SELECT queries.
     * This is a default implementation compatible to MySQL and PostgreSQL.
     * Other database implementations should override this function.
     *
     * @param string $query
     * @param int $limit
     * @param int $offset
     * @return  string
     */
    public function handleLimitParameter($query, $limit = 0, $offset = 0)
    {
        $limit = \intval($limit);
        $offset = \intval($offset);
        if ($limit < 0) {
            throw new \InvalidArgumentException('The limit must not be negative.');
        }
        if ($offset < 0) {
            throw new \InvalidArgumentException('The offset must not be negative.');
        }

        if ($limit != 0) {
            $query = \preg_replace(
                '~(\s+FOR\s+UPDATE\s*)?$~',
                " LIMIT " . $limit . ($offset ? " OFFSET " . $offset : '') . "\\0",
                $query,
                1
            );
        }

        return $query;
    }

    /**
     * Returns the number of the last error.
     *
     * @return  int
     */
    public function getErrorNumber()
    {
        if ($this->pdo !== null) {
            return $this->pdo->errorCode();
        }

        return 0;
    }

    /**
     * Returns the description of the last error.
     *
     * @return  string
     */
    public function getErrorDesc()
    {
        if ($this->pdo !== null) {
            $errorInfoArray = $this->pdo->errorInfo();
            if (isset($errorInfoArray[2])) {
                return $errorInfoArray[2];
            }
        }

        return '';
    }

    /**
     * Returns the current database type.
     *
     * @return  string
     */
    public function getDBType()
    {
        return static::class;
    }

    /**
     * Escapes a string for use in sql query.
     *
     * @param string $string
     * @return  string
     */
    public function escapeString($string)
    {
        return \addslashes($string);
    }

    /**
     * Escapes a value for use in a `LIKE` condition.
     *
     * @since 6.0
     */
    public function escapeLikeValue(string $value): string
    {
        return \addcslashes($value, "\\%_");
    }

    /**
     * Returns the sql version.
     *
     * @return  string
     */
    public function getVersion()
    {
        try {
            if ($this->pdo !== null) {
                return $this->pdo->getAttribute(\PDO::ATTR_SERVER_VERSION);
            }
        } catch (\PDOException $e) {
        }

        return 'unknown';
    }

    /**
     * Returns the database name.
     *
     * @return  string
     */
    public function getDatabaseName()
    {
        return $this->database;
    }

    /**
     * Returns the name of the database user.
     *
     * @return  string      user name
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Returns the amount of executed sql queries.
     *
     * @return  int
     */
    public function getQueryCount()
    {
        return $this->queryCount;
    }

    /**
     * Increments the query counter by one.
     */
    public function incrementQueryCount()
    {
        $this->queryCount++;
    }

    /**
     * Returns a database editor object.
     *
     * @return  DatabaseEditor
     */
    public function getEditor()
    {
        if ($this->editor === null) {
            $this->editor = new $this->editorClassName($this);
        }

        return $this->editor;
    }

    /**
     * Returns true if this database type is supported.
     *
     * @return  bool
     */
    public static function isSupported()
    {
        return false;
    }

    /**
     * Sets default connection attributes.
     */
    protected function setAttributes()
    {
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(\PDO::ATTR_CASE, \PDO::CASE_NATURAL);
        $this->pdo->setAttribute(\PDO::ATTR_STRINGIFY_FETCHES, false);
    }
}
