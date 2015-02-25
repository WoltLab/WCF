<?php
namespace wcf\system\database;
use wcf\system\database\statement\PreparedStatement;
use wcf\system\exception\SystemException;
use wcf\util\StringUtil;

/**
 * DatabaseException is a specific SystemException for database errors.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.database
 * @category	Community Framework
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
	
	/**
	 * Prints the error page.
	 */
	public function show() {
		$this->information .= '<b>sql type:</b> ' . StringUtil::encodeHTML($this->getDBType()) . '<br />';
		$this->information .= '<b>sql error:</b> ' . StringUtil::encodeHTML($this->getErrorDesc()) . '<br />';
		$this->information .= '<b>sql error number:</b> ' . StringUtil::encodeHTML($this->getErrorNumber()) . '<br />';
		$this->information .= '<b>sql version:</b> ' . StringUtil::encodeHTML($this->getSQLVersion()) . '<br />';
		if ($this->preparedStatement !== null) {
			$this->information .= '<b>sql query:</b> ' . StringUtil::encodeHTML($this->preparedStatement->getSQLQuery()) . '<br />';
			$parameters = $this->preparedStatement->getSQLParameters();
			if (!empty($parameters)) {
				foreach ($parameters as $index => $parameter) {
					$this->information .= '<b>sql query parameter ' . $index . ':</b>' . StringUtil::encodeHTML($parameter) . '<br />';
				}
			}
		}
		else if ($this->sqlQuery !== null) {
			$this->information .= '<b>sql query:</b> ' . StringUtil::encodeHTML($this->sqlQuery) . '<br />';
		}
		
		parent::show();
	}
}
