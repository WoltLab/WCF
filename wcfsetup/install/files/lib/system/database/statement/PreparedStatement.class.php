<?php
namespace wcf\system\database\statement;
use wcf\system\benchmark\Benchmark;
use wcf\system\database\Database;
use wcf\system\database\DatabaseException;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

/**
 * Represents a prepared statements based upon pdo statements.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.database.statement
 * @category	Community Framework
 */
class PreparedStatement {
	/**
	 * database object
	 * @var	\wcf\system\database\Database
	 */
	protected $database = null;
	
	/**
	 * SQL query parameters
	 * @var	array
	 */
	protected $parameters = array();
	
	/**
	 * pdo statement object
	 * @var	\PDOStatement
	 */
	protected $pdoStatement = null;
	
	/**
	 * SQL query
	 * @var	string
	 */
	protected $query = '';
	
	/**
	 * Creates a new PreparedStatement object.
	 * 
	 * @param	\wcf\system\database\Database	$database
	 * @param	\PDOStatement			$pdoStatement
	 * @param	string				$query		SQL query
	 */
	public function __construct(Database $database, \PDOStatement $pdoStatement, $query = '') {
		$this->database = $database;
		$this->pdoStatement = $pdoStatement;
		$this->query = $query;
	}
	
	/**
	 * Delegates inaccessible methods calls to the decorated object.
	 * 
	 * @param	string		$name
	 * @param	array		$arguments
	 * @return	mixed
	 */
	public function __call($name, $arguments) {
		if (!method_exists($this->pdoStatement, $name)) {
			throw new SystemException("unknown method '".$name."'");
		}
		
		try {
			return call_user_func_array(array($this->pdoStatement, $name), $arguments);
		}
		catch (\PDOException $e) {
			throw new DatabaseException('Could not handle prepared statement: '.$e->getMessage(), $this->database, $this);
		}
	}
	
	/**
	 * Executes a prepared statement.
	 * 
	 * @param	array		$parameters
	 */
	public function execute(array $parameters = array()) {
		$this->parameters = $parameters;
		$this->database->incrementQueryCount();
		
		try {
			if (WCF::benchmarkIsEnabled()) Benchmark::getInstance()->start($this->query, Benchmark::TYPE_SQL_QUERY);
			
			if (empty($parameters)) $this->pdoStatement->execute();
			else $this->pdoStatement->execute($parameters);
			
			if (WCF::benchmarkIsEnabled()) Benchmark::getInstance()->stop();
		}
		catch (\PDOException $e) {
			if (WCF::benchmarkIsEnabled()) Benchmark::getInstance()->stop();
			
			throw new DatabaseException('Could not execute prepared statement: '.$e->getMessage(), $this->database, $this);
		}
	}
	
	/**
	 * Executes a prepared statement.
	 * 
	 * @deprecated	2.1 - Please use execute() instead
	 * @param	array		$parameters
	 */
	public function executeUnbuffered(array $parameters = array()) {
		$this->execute($parameters);
	}
	
	/**
	 * Fetches the next row from a result set in an array.
	 * 
	 * @param	integer		$type		fetch type
	 * @return	mixed
	 */
	public function fetchArray($type = null) {
		// get fetch style
		if ($type === null) $type = \PDO::FETCH_ASSOC;
		
		return $this->fetch($type);
	}
	
	/**
	 * Fetches the next row from a result set in an array.
	 * Closes the 'cursor' afterwards to free up the connection
	 * for new queries.
	 * Note: It is not possible to fetch further rows after calling
	 * this method!
	 * 
	 * @param	integer		$type		fetch type
	 * @return	mixed
	 * @see		\wcf\system\database\statement\PreparedStatement::fetchArray()
	 */
	public function fetchSingleRow($type = null) {
		$row = $this->fetchArray($type);
		$this->closeCursor();
		
		return $row;
	}
	
	/**
	 * Returns the specified column of the next row of a result set.
	 * Closes the 'cursor' afterwards to free up the connection
	 * for new queries.
	 * Note: It is not possible to fetch further rows after calling
	 * this method!
	 * 
	 * @param	integer		$columnNumber
	 * @return	mixed
	 * @see		\PDOStatement::fetchColumn()
	 */
	public function fetchSingleColumn($columnNumber = 0) {
		$column = $this->fetchColumn($columnNumber);
		$this->closeCursor();
		
		return $column;
	}
	
	/**
	 * Fetches the next row from a result set in a database object.
	 * 
	 * @param	string			$className
	 * @return	\wcf\data\DatabaseObject
	 */
	public function fetchObject($className) {
		$row = $this->fetchArray();
		if ($row !== false) {
			return new $className(null, $row);
		}
		
		return null;
	}
	
	/**
	 * Fetches the all rows from a result set into database objects.
	 * 
	 * @param	string			$className
	 * @return	array<\wcf\data\DatabaseObject>
	 */
	public function fetchObjects($className) {
		$objects = array();
		while ($object = $this->fetchObject($className)) {
			$objects[] = $object;
		}
		
		return $objects;
	}
	
	/**
	 * Counts number of affected rows by the last sql statement (INSERT, UPDATE or DELETE).
	 * 
	 * @return	integer		number of affected rows
	 */
	public function getAffectedRows() {
		try {
			return $this->pdoStatement->rowCount();
		}
		catch (\PDOException $e) {
			throw new DatabaseException("Can not fetch affected rows: ".$e->getMessage(), $this);
		}
	}
	
	/**
	 * Returns the number of the last error.
	 * 
	 * @return	integer
	 */
	public function getErrorNumber() {
		if ($this->pdoStatement !== null) return $this->pdoStatement->errorCode();
		
		return 0;
	}
	
	/**
	 * Returns the description of the last error.
	 * 
	 * @return	string
	 */
	public function getErrorDesc() {
		if ($this->pdoStatement !== null) {
			$errorInfoArray = $this->pdoStatement->errorInfo();
			if (isset($errorInfoArray[2])) return $errorInfoArray[2];
		}
		
		return '';
	}
	
	/**
	 * Returns the SQL query of this statement.
	 * 
	 * @return	string
	 */
	public function getSQLQuery() {
		return $this->query;
	}
	
	/**
	 * Returns the SQL query parameters of this statement.
	 * 
	 * @return	array
	 */
	public function getSQLParameters() {
		return $this->parameters;
	}
}
