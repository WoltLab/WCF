<?php
namespace wcf\system\database\table\column;

/**
 * Every database table column whose values supports decimals must implement this interface.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Database\Table\Column
 * @since	5.2
 */
interface IDecimalsDatabaseTableColumn extends ILengthDatabaseTableColumn {
	/**
	 * Sets the number of decimals the database table column supports or unsets the previously
	 * set value if `null` is passed and returns this column.
	 * 
	 * @param	null|int	$decimals
	 * @return	$this
	 */
	public function decimals($decimals);
	
	/**
	 * Returns the number of decimals the database table column supports or `null` if the number
	 * of decimals has not be specified.
	 * 
	 * @return	null|int
	 */
	public function getDecimals();
}
