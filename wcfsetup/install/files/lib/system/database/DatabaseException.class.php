<?php
namespace wcf\system\database;
use wcf\system\database\statement\PreparedStatement;
use wcf\system\exception\SystemException;

/**
 * DatabaseException is a specific SystemException for database errors.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Database
 * @deprecated	3.0 - Use \wcf\system\database\exception\DatabaseException
 */
class DatabaseException extends SystemException {
	/**
	 * error number
	 * @var	integer
	 */
	protected $errorNumber = null;
	
	/**
	 * error description
	 * @var	string
	 */
	protected $errorDesc = null;
	
	/**
	 * sql version number
	 * @var	string
	 */
	protected $sqlVersion = null;
	
	/**
	 * sql type
	 * @var	string
	 */
	protected $DBType = null;
	
	/**
	 * database object
	 * @var	\wcf\system\database\Database
	 */
	protected $db = null;
	
	/**
	 * prepared statement object
	 * @var	\wcf\system\database\statement\PreparedStatement
	 */
	protected $preparedStatement = null;
	
	/**
	 * SQL query if prepare() failed
	 * @var	string
	 */
	protected $sqlQuery = null;
	
	/**
	 * Creates a new DatabaseException.
	 * 
	 * @param	string							$message		error message
	 * @param	\wcf\system\database\Database				$db			affected db object
	 * @param	\wcf\system\database\statement\PreparedStatement	$preparedStatement	affected prepared statement
	 * @param	string							$sqlQuery		SQL query if prepare() failed
	 */
	public function __construct($message, Database $db, PreparedStatement $preparedStatement = null, $sqlQuery = null) {
		$this->db = $db;
		$this->DBType = $db->getDBType();
		$this->preparedStatement = $preparedStatement;
		$this->sqlQuery = $sqlQuery;
		
		// prefer errors from prepared statement
		if ($this->preparedStatement !== null && $this->preparedStatement->getErrorNumber()) {
			$this->errorNumber = $this->preparedStatement->getErrorNumber();
			$this->errorDesc = $this->preparedStatement->getErrorDesc();
		}
		else {
			$this->errorNumber = $this->db->getErrorNumber();
			$this->errorDesc = $this->db->getErrorDesc();
		}
		
		parent::__construct($message, intval($this->errorNumber));
	}
	
	/**
	 * Returns the error number of this exception.
	 * 
	 * @return	integer
	 */
	public function getErrorNumber() {
		return $this->errorNumber;
	}
	
	/**
	 * Returns the error description of this exception.
	 * 
	 * @return	string
	 */
	public function getErrorDesc() {
		return $this->errorDesc;
	}
	
	/**
	 * Returns the current sql version of the database.
	 * 
	 * @return	string
	 */
	public function getSQLVersion() {
		if ($this->sqlVersion === null) {
			try {
				$this->sqlVersion = $this->db->getVersion();
			}
			catch (DatabaseException $e) {
				$this->sqlVersion = 'unknown';
			}
		}
		
		return $this->sqlVersion;
	}
	
	/**
	 * Returns the sql type of the active database.
	 * 
	 * @return	string
	 */
	public function getDBType() {
		return $this->DBType;
	}
}
