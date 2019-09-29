<?php
namespace wcf\system\database\table\column;

/**
 * Every database table column whose values can be auto-incremented must implement this interface.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Database\Table\Column
 * @since	5.2
 */
interface IAutoIncrementDatabaseTableColumn {
	/**
	 * Sets if the values of the database table column are auto-increment and returns this column.
	 * 
	 * @param	bool	$autoIncrement
	 * @return	$this
	 */
	public function autoIncrement($autoIncrement = true);
	
	/**
	 * Returns `true` if the values of the database table column are auto-increment.
	 * 
	 * @return	bool
	 */
	public function isAutoIncremented();
}
