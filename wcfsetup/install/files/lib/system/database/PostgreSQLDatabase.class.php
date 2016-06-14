<?php
namespace wcf\system\database;
use wcf\system\database\editor\PostgreSQLDatabaseEditor;
use wcf\system\database\exception\DatabaseException as GenericDatabaseException;
use wcf\util\StringStack;

/**
 * This is the database implementation for PostgreSQL.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Database
 */
class PostgreSQLDatabase extends Database {
	/**
	 * @inheritDoc
	 */
	protected $editorClassName = PostgreSQLDatabaseEditor::class;
	
	/**
	 * @inheritDoc
	 */
	public function connect() {
		if (!$this->port) $this->port = 5432; // postgresql default port
		
		try {
			$this->pdo = new \PDO('pgsql:host='.$this->host.';port='.$this->port.';dbname='.$this->database, $this->user, $this->password);
			$this->setAttributes();
		}
		catch (\PDOException $e) {
			throw new GenericDatabaseException("Connecting to PostgreSQL server '".$this->host."' failed", $e);
		}
		
		// set connection character set
		$this->setCharset();
	}
	
	/**
	 * Sets the charset of the database connection.
	 */
	protected function setCharset() {
		try {
			$statement = $this->prepareStatement("SET NAMES 'UTF8'");
			$statement->execute();
		}
		catch (DatabaseException $e) {
			// ignore
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public static function isSupported() {
		return (extension_loaded('PDO') && extension_loaded('pdo_pgsql'));
	}
	
	/**
	 * @inheritDoc
	 */
	public function prepareStatement($statement, $limit = 0, $offset = 0) {
		$statement = self::fixQuery($statement);
		return parent::prepareStatement($statement, $limit, $offset);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getInsertID($table, $field) {
		try {
			return $this->pdo->lastInsertId('"' . $table . '_' . $field . '_seq"');
		}
		catch (\PDOException $e) {
			throw new GenericDatabaseException("Can not fetch last insert id", $this);
		}
	}
	
	/**
	 * By default identifiers are case insensitive in PostgreSQL.
	 * We need to double quote identifiers here automatically.
	 * 
	 * @param	string		$query
	 * @return	string		$query
	 */
	public static function fixQuery($query) {
		// replace quotes
		$query = preg_replace_callback('~\'([^\'\\\\]+|\\\\.)*\'~', ['self', 'replaceQuotesCallback'], $query);
		$query = preg_replace_callback('~"([^"\\\\]+|\\\\.)*"~', ['self', 'replaceQuotesCallback'], $query);
		
		// double quote identifiers (column & table names ...)
		$query = preg_replace('~(?<=^|\s|\.|\(|,)([A-Za-z0-9_-]*[a-z]{1}[A-Za-z0-9_-]*)(?=$|\s|\.|\)|,|=)~', '"\\1"', $query);
		
		// rename LIKE to ILIKE for case-insensitive comparisons
		$query = preg_replace('/(?<=\s)LIKE(?=\s)/si', 'ILIKE', $query);
		
		// reinsert quotes
		$query = StringStack::reinsertStrings($query, 'postgresQuotes');
		
		return $query;
	}
	
	/**
	 * @inheritDoc
	 */
	public function escapeString($string) {
		$string = str_replace("\x00", "\\x00", $string); // escape nul bytes
		return parent::escapeString($string);
	}
	
	/**
	 * Callback function used in fixQuery().
	 * 
	 * @param	string[]	$matches
	 * @return	string
	 */
	private static function replaceQuotesCallback($matches) {
		return StringStack::pushToStringStack($matches[0], 'postgresQuotes');
	}
}
