<?php
namespace wcf\system\database\editor;
use wcf\system\database\Database;

/**
 * This is an abstract implementation of a database editor class.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.database.editor
 * @category 	Community Framework
 */
abstract class DatabaseEditor {
	/**
	 * database object
	 * @var	wcf\system\database\Database
	 */
	protected $dbObj = null;

	/**
	 * Creates a new DatabaseEditor object.
	 *
	 * @param	Database	$dbObj
	 */
	public function __construct(Database $dbObj) {
		$this->dbObj = $dbObj;
	}
	
	/**
	 * Returns all existing tablenames whose table name is like the given tabe
	 * name.
	 * 
	 * @param	string		$likeTableName
	 * @return 	array
	 */
	public abstract function getTableNames($likeTableName = null);
	
	/**
	 * Returns the columns of a table.
	 * 
	 * @param	string		$tableName
	 * @return	array
	 */
	public abstract function getColumns($tableName);
	
	/**
	 * Returns the indices of a table.
	 * 
	 * @param	string		$tableName
	 * @return	array
	 */
	public abstract function getIndices($tableName);
	
	/**
	 * Creates a new database table.
	 * 
	 * @param	string		$tableName
	 * @param	array		$columns
	 * @param	array		$indices
	 */
	public abstract function createTable($tableName, $columns, $indices = array());
	
	/**
	 * Drops a database table.
	 * 
	 * @param	string		$tableName
	 */
	public abstract function dropTable($tableName);
	
	/**
	 * Adds a new column to an existing database table.
	 * 
	 * @param	string		$tableName
	 * @param	string		$columnName
	 * @param	array		$columnData
	 */
	public abstract function addColumn($tableName, $columnName, $columnData);
	
	/**
	 * Alters an existing column.
	 *
	 * @param	string		$tableName
	 * @param	string		$oldColumnName
	 * @param	string		$newColumnName
	 * @param	array		$newColumnData
	 */
	public abstract function alterColumn($tableName, $oldColumnName, $newColumnName, $newColumnData);
	
	/**
	 * Drops an existing column.
	 * 
	 * @param	string		$tableName
	 * @param	string		$columnName
	 */
	public abstract function dropColumn($tableName, $columnName);
	
	/**
	 * Adds a new index to an existing database table.
	 * 
	 * @param	string		$tableName
	 * @param	string		$indexName
	 * @param	array		$indexData
	 */
	public abstract function addIndex($tableName, $indexName, $indexData);
	
	/**
	 * Adds a new foreign key to an existing database table.
	 * 
	 * @param	string		$tableName
	 * @param	string		$indexName
	 * @param	array		$indexData
	 */
	public abstract function addForeignKey($tableName, $indexName, $indexData);
	
	/**
	 * Drops an existing index.
	 * 
	 * @param	string		$tableName
	 * @param	string		$indexName
	 */
	public abstract function dropIndex($tableName, $indexName);
	
	/**
	 * Drops an existing foreign key.
	 * 
	 * @param	string		$tableName
	 * @param	string		$indexName
	 */
	public abstract function dropForeignKey($tableName, $indexName);
	
	/**
	 * Drops all given databases.
	 * 
	 * @param	array		$conflictedTables
	 */
	public abstract function dropConflictedTables(array $conflictedTables);
}
