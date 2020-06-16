<?php
namespace wcf\system\database\table\column;

/**
 * Represents a column of a database table.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Database\Table\Column
 * @since	5.2
 */
interface IDatabaseTableColumn {
	/**
	 * Sets the default value of the column and returns the column.
	 * 
	 * @param	mixed	$defaultValue
	 * @return	$this
	 */
	public function defaultValue($defaultValue);

	/**
	 * Marks the column to be dropped and returns the column.
	 * 
	 * @return	$this
	 */
	public function drop();
	
	/**
	 * Returns the data used by `DatabaseEditor` to add the column to a table.
	 *
	 * @return	array
	 */
	public function getData();
	
	/**
	 * Returns the default value of the column.
	 * 
	 * @return	$this
	 */
	public function getDefaultValue();
	
	/**
	 * Returns the name of the column.
	 * 
	 * @return	string
	 */
	public function getName();
	
	/**
	 * Returns the type of the column.
	 * 
	 * @return	string
	 */
	public function getType();
	
	/**
	 * Returns `true` if the values of the column cannot be `null`.
	 * 
	 * @return	bool
	 */
	public function isNotNull();
	
	/**
	 * Sets the name of the column and returns the column.
	 * 
	 * @param	string		$name
	 * @return	$this
	 */
	public function name($name);
	
	/**
	 * Sets if the values of the column cannot be `null`.
	 * 
	 * @param	bool	$notNull
	 * @return	$this
	 */
	public function notNull($notNull = true);
	
	/**
	 * Returns `true` if the column will be dropped.
	 *
	 * @return	bool
	 */
	public function willBeDropped();
	
	/**
	 * Returns a `DatabaseTableColumn` object with the given name.
	 * 
	 * @param	string		$name
	 * @return	$this
	 */
	public static function create($name);

	/**
	 * Returns a `DatabaseTableColumn` object with the given name and data.
	 * 
	 * @param	string		$name
	 * @param	array		$data		data returned by `DatabaseEditor::getColumns()`
	 * @return	$this
	 */
	public static function createFromData($name, array $data);
}
