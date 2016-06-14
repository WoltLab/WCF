<?php
namespace wcf\system\database\editor;
use wcf\system\database\Database;

/**
 * Abstract implementation of a database editor.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Database\Editor
 */
abstract class DatabaseEditor {
	/**
	 * database object
	 * @var	\wcf\system\database\Database
	 */
	protected $dbObj = null;
	
	/**
	 * Creates a new DatabaseEditor object.
	 * 
	 * @param	\wcf\system\database\Database	$dbObj
	 */
	public function __construct(Database $dbObj) {
		$this->dbObj = $dbObj;
	}
	
	/**
	 * Returns all existing table names.
	 * 
	 * @return	array		$existingTables
	 */
	abstract public function getTableNames();
	
	/**
	 * Returns the columns of a table.
	 * 
	 * @param	string		$tableName
	 * @return	array		$columns
	 */
	abstract public function getColumns($tableName);
	
	/**
	 * Returns the indices of a table.
	 * 
	 * @param	string		$tableName
	 * @return	array		$indices
	 */
	abstract public function getIndices($tableName);
	
	/**
	 * Creates a new database table.
	 * 
	 * @param	string		$tableName
	 * @param	array		$columns
	 * @param	array		$indices
	 */
	abstract public function createTable($tableName, $columns, $indices = []);
	
	/**
	 * Drops a database table.
	 * 
	 * @param	string		$tableName
	 */
	abstract public function dropTable($tableName);
	
	/**
	 * Adds a new column to an existing database table.
	 * 
	 * @param	string		$tableName
	 * @param	string		$columnName
	 * @param	array		$columnData
	 */
	abstract public function addColumn($tableName, $columnName, $columnData);
	
	/**
	 * Alters an existing column.
	 * 
	 * @param	string		$tableName
	 * @param	string		$oldColumnName
	 * @param	string		$newColumnName
	 * @param	array		$newColumnData
	 */
	abstract public function alterColumn($tableName, $oldColumnName, $newColumnName, $newColumnData);
	
	/**
	 * Drops an existing column.
	 * 
	 * @param	string		$tableName
	 * @param	string		$columnName
	 */
	abstract public function dropColumn($tableName, $columnName);
	
	/**
	 * Adds a new index to an existing database table.
	 * 
	 * @param	string		$tableName
	 * @param	string		$indexName
	 * @param	array		$indexData
	 */
	abstract public function addIndex($tableName, $indexName, $indexData);
	
	/**
	 * Adds a new foreign key to an existing database table.
	 * 
	 * @param	string		$tableName
	 * @param	string		$indexName
	 * @param	array		$indexData
	 */
	abstract public function addForeignKey($tableName, $indexName, $indexData);
	
	/**
	 * Drops an existing index.
	 * 
	 * @param	string		$tableName
	 * @param	string		$indexName
	 */
	abstract public function dropIndex($tableName, $indexName);
	
	/**
	 * Drops an existing foreign key.
	 * 
	 * @param	string		$tableName
	 * @param	string		$indexName
	 */
	abstract public function dropForeignKey($tableName, $indexName);
}
