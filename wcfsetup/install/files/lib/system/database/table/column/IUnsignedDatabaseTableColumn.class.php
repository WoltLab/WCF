<?php
namespace wcf\system\database\table\column;

/**
 * Every database table column whose values can be unsigned must implement this interface.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Database\Table\Column
 * @since	5.2
 */
interface IUnsignedDatabaseTableColumn extends IDatabaseTableColumn {
	/**
	 * Returns `true` if the values of the database table column are unsigned.
	 *
	 * @return	bool
	 */
	public function isUnsigned();
	
	/**
	 * Sets if the values of the database table column are unsigned and returns this column.
	 *
	 * @param	bool	$unsigned
	 * @return	$this
	 */
	public function unsigned($unsigned = true);
}
