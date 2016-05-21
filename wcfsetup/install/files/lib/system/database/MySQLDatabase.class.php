<?php
namespace wcf\system\database;
use wcf\system\database\exception\DatabaseException as GenericDatabaseException;

/**
 * This is the database implementation for MySQL 5.1 or higher using PDO.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.database
 * @category	Community Framework
 */
class MySQLDatabase extends Database {
	/**
	 * @see	\wcf\system\database\Database::$editorClassName
	 */
	protected $editorClassName = 'wcf\system\database\editor\MySQLDatabaseEditor';
	
	/**
	 * @see	\wcf\system\database\Database::connect()
	 */
	public function connect() {
		if (!$this->port) $this->port = 3306; // mysql default port
		
		try {
			$driverOptions = [
				\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"
			];
			if (!$this->failsafeTest) {
				$driverOptions = [
					\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8', SESSION sql_mode = 'ANSI,ONLY_FULL_GROUP_BY,STRICT_ALL_TABLES'"
				];
			}
			
			// disable prepared statement emulation since MySQL 5.1.17 is the minimum required version
			$driverOptions[\PDO::ATTR_EMULATE_PREPARES] = false;
			
			// throw PDOException instead of dumb false return values
			$driverOptions[\PDO::ATTR_ERRMODE] = \PDO::ERRMODE_EXCEPTION;
			
			$this->pdo = new \PDO('mysql:host='.$this->host.';port='.$this->port.';dbname='.$this->database, $this->user, $this->password, $driverOptions);
			$this->setAttributes();
		}
		catch (\PDOException $e) {
			throw new GenericDatabaseException("Connecting to MySQL server '".$this->host."' failed", $e);
		}
	}
	
	/**
	 * @see	\wcf\system\database\Database::isSupported()
	 */
	public static function isSupported() {
		return (extension_loaded('PDO') && extension_loaded('pdo_mysql'));
	}
	
	/**
	 * @see	\wcf\system\database\PDODatabase::setAttributes()
	 */
	protected function setAttributes() {
		parent::setAttributes();
		$this->pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
	}
}
