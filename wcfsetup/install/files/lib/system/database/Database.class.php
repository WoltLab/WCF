<?php
namespace wcf\system\database;
use wcf\system\benchmark\Benchmark;
use wcf\system\database\editor\DatabaseEditor;
use wcf\system\database\exception\DatabaseException as GenericDatabaseException;
use wcf\system\database\exception\DatabaseQueryException;
use wcf\system\database\exception\DatabaseTransactionException;
use wcf\system\database\statement\PreparedStatement;
use wcf\system\WCF;

/**
 * Abstract implementation of a database access class using PDO.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Database
 */
abstract class Database {
	/**
	 * name of the class used for prepared statements
	 * @var	string
	 */
	protected $preparedStatementClassName = PreparedStatement::class;
	
	/**
	 * name of the database editor class
	 * @var	string
	 */
	protected $editorClassName = DatabaseEditor::class;
	
	/**
	 * sql server hostname
	 * @var	string
	 */
	protected $host = '';
	
	/**
	 * sql server post
	 * @var	integer
	 */
	protected $port = 0;
	
	/**
	 * sql server login name
	 * @var	string
	 */
	protected $user = '';
	
	/**
	 * sql server login password
	 * @var	string
	 */
	protected $password = '';
	
	/**
	 * database name
	 * @var	string
	 */
	protected $database = '';
	
	/**
	 * enables failsafe connection
	 * @var	boolean
	 */
	protected $failsafeTest = false;
	
	/**
	 * number of executed queries
	 * @var	integer
	 */
	protected $queryCount = 0;
	
	/**
	 * database editor object
	 * @var	\wcf\system\database\editor\DatabaseEditor
	 */
	protected $editor = null;
	
	/**
	 * pdo object
	 * @var	\PDO
	 */
	protected $pdo = null;
	
	/**
	 * amount of active transactions
	 * @var	integer
	 */
	protected $activeTransactions = 0;
	
	/**
	 * Creates a Dabatase Object.
	 * 
	 * @param	string		$host			SQL database server host address
	 * @param	string		$user			SQL database server username
	 * @param	string		$password		SQL database server password
	 * @param	string		$database		SQL database server database name
	 * @param	integer		$port			SQL database server port
	 * @param	boolean		$failsafeTest
	 */
	public function __construct($host, $user, $password, $database, $port, $failsafeTest = false) {
		$this->host = $host;
		$this->port = $port;
		$this->user = $user;
		$this->password = $password;
		$this->database = $database;
		$this->failsafeTest = $failsafeTest;
		
		// connect database
		$this->connect();
	}
	
	/**
	 * Connects to database server.
	 */
	abstract public function connect();
	
	/**
	 * Returns ID from last insert.
	 * 
	 * @param	string		$table
	 * @param	string		$field
	 * @return	integer
	 * @throws	DatabaseException
	 */
	public function getInsertID($table, $field) {
		try {
			return $this->pdo->lastInsertId();
		}
		catch (\PDOException $e) {
			throw new GenericDatabaseException("Cannot fetch last insert id", $e);
		}
	}
	
	/**
	 * Initiates a transaction.
	 * 
	 * @return	boolean		true on success
	 * @throws	DatabaseTransactionException
	 */
	public function beginTransaction() {
		try {
			if ($this->activeTransactions === 0) {
				if (WCF::benchmarkIsEnabled()) Benchmark::getInstance()->start("BEGIN", Benchmark::TYPE_SQL_QUERY);
				$result = $this->pdo->beginTransaction();
			}
			else {
				if (WCF::benchmarkIsEnabled()) Benchmark::getInstance()->start("SAVEPOINT level".$this->activeTransactions, Benchmark::TYPE_SQL_QUERY);
				$result = $this->pdo->exec("SAVEPOINT level".$this->activeTransactions) !== false;
			}
			if (WCF::benchmarkIsEnabled()) Benchmark::getInstance()->stop();
			
			$this->activeTransactions++;
			
			return $result;
		}
		catch (\PDOException $e) {
			throw new DatabaseTransactionException("Could not begin transaction", $e);
		}
	}
	
	/**
	 * Commits a transaction and returns true if the transaction was successfull.
	 * 
	 * @return	boolean
	 * @throws	DatabaseTransactionException
	 */
	public function commitTransaction() {
		if ($this->activeTransactions === 0) return false;
		
		try {
			$this->activeTransactions--;
			
			if ($this->activeTransactions === 0) {
				if (WCF::benchmarkIsEnabled()) Benchmark::getInstance()->start("COMMIT", Benchmark::TYPE_SQL_QUERY);
				$result = $this->pdo->commit();
			}
			else {
				if (WCF::benchmarkIsEnabled()) Benchmark::getInstance()->start("RELEASE SAVEPOINT level".$this->activeTransactions, Benchmark::TYPE_SQL_QUERY);
				$result = $this->pdo->exec("RELEASE SAVEPOINT level".$this->activeTransactions) !== false;
			}
			
			if (WCF::benchmarkIsEnabled()) Benchmark::getInstance()->stop();
			
			return $result;
		}
		catch (\PDOException $e) {
			throw new DatabaseTransactionException("Could not commit transaction", $e);
		}
	}
	
	/**
	 * Rolls back a transaction and returns true if the rollback was successfull.
	 * 
	 * @return	boolean
	 * @throws	DatabaseTransactionException
	 */
	public function rollBackTransaction() {
		if ($this->activeTransactions === 0) return false;
		
		try {
			$this->activeTransactions--;
			if ($this->activeTransactions === 0) {
				if (WCF::benchmarkIsEnabled()) Benchmark::getInstance()->start("ROLLBACK", Benchmark::TYPE_SQL_QUERY);
				$result = $this->pdo->rollBack();
			}
			else {
				if (WCF::benchmarkIsEnabled()) Benchmark::getInstance()->start("ROLLBACK TO SAVEPOINT level".$this->activeTransactions, Benchmark::TYPE_SQL_QUERY);
				$result = $this->pdo->exec("ROLLBACK TO SAVEPOINT level".$this->activeTransactions) !== false;
			}
			
			if (WCF::benchmarkIsEnabled()) Benchmark::getInstance()->stop();
			
			return $result;
		}
		catch (\PDOException $e) {
			throw new DatabaseTransactionException("Could not roll back transaction", $e);
		}
	}
	
	/**
	 * Prepares a statement for execution and returns a statement object.
	 * 
	 * @param	string			$statement
	 * @param	integer			$limit
	 * @param	integer			$offset
	 * @return	PreparedStatement
	 * @throws	DatabaseQueryException
	 */
	public function prepareStatement($statement, $limit = 0, $offset = 0) {
		$statement = $this->handleLimitParameter($statement, $limit, $offset);
		
		try {
			$pdoStatement = $this->pdo->prepare($statement);
			
			return new $this->preparedStatementClassName($this, $pdoStatement, $statement);
		}
		catch (\PDOException $e) {
			throw new DatabaseQueryException("Could not prepare statement '".$statement."'", $e);
		}
	}
	
	/**
	 * Handles the limit and offset parameter in SELECT queries.
	 * This is a default implementation compatible to MySQL and PostgreSQL.
	 * Other database implementations should override this function. 
	 * 
	 * @param	string		$query
	 * @param	integer		$limit
	 * @param	integer		$offset
	 * @return	string
	 */
	public function handleLimitParameter($query, $limit = 0, $offset = 0) {
		if ($limit != 0) {
			$query = preg_replace('~(\s+FOR\s+UPDATE\s*)?$~', " LIMIT " . $limit . ($offset ? " OFFSET " . $offset : '') . "\\0", $query, 1);
		}
		
		return $query;
	}
	
	/**
	 * Returns the number of the last error.
	 * 
	 * @return	integer
	 */
	public function getErrorNumber() {
		if ($this->pdo !== null) return $this->pdo->errorCode();
		return 0;
	}
	
	/**
	 * Returns the description of the last error.
	 * 
	 * @return	string
	 */
	public function getErrorDesc() {
		if ($this->pdo !== null) {
			$errorInfoArray = $this->pdo->errorInfo();
			if (isset($errorInfoArray[2])) return $errorInfoArray[2];
		}
		return '';
	}
	
	/**
	 * Gets the current database type.
	 * 
	 * @return	string
	 */
	public function getDBType() {
		return get_class($this);
	}
	
	/**
	 * Escapes a string for use in sql query.
	 * 
	 * @param	string		$string
	 * @return	string
	 */
	public function escapeString($string) {
		return addslashes($string);
	}
	
	/**
	 * Gets the sql version.
	 * 
	 * @return	string
	 */
	public function getVersion() {
		try {
			if ($this->pdo !== null) {
				return $this->pdo->getAttribute(\PDO::ATTR_SERVER_VERSION);
			}
		}
		catch (\PDOException $e) {}
		
		return 'unknown';
	}
	
	/**
	 * Gets the database name.
	 * 
	 * @return	string
	 */
	public function getDatabaseName() {
		return $this->database;
	}
	
	/**
	 * Returns the name of the database user.
	 * 
	 * @return	string		user name
	 */
	public function getUser() {
		return $this->user;
	}
	
	/**
	 * Returns the amount of executed sql queries.
	 * 
	 * @return	integer
	 */
	public function getQueryCount() {
		return $this->queryCount;
	}
	
	/**
	 * Increments the query counter by one.
	 */
	public function incrementQueryCount() {
		$this->queryCount++;
	}
	
	/**
	 * Returns a database editor object.
	 * 
	 * @return	\wcf\system\database\editor\DatabaseEditor
	 */
	public function getEditor() {
		if ($this->editor === null) {
			$this->editor = new $this->editorClassName($this);
		}
		
		return $this->editor;
	}
	
	/**
	 * Returns true if this database type is supported.
	 * 
	 * @return	boolean
	 */
	public static function isSupported() {
		return false;
	}
	
	/**
	 * Sets default connection attributes.
	 */
	protected function setAttributes() {
		$this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		$this->pdo->setAttribute(\PDO::ATTR_CASE, \PDO::CASE_NATURAL);
		$this->pdo->setAttribute(\PDO::ATTR_STRINGIFY_FETCHES, false);
	}
}
