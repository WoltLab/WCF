<?php
namespace wcf\system\database\table\column;

/**
 * Every database table column that supports specifying a (maximum) value length must implement this
 * interface.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Database\Table\Column
 * @since	5.2
 */
interface ILengthDatabaseTableColumn extends IDatabaseTableColumn {
	/**
	 * Returns the (maximum) length of the column's values or `null` if no length has been set.
	 * 
	 * @return	null|int
	 */
	public function getLength();
	
	/**
	 * Sets the (maximum) length of the column's values.
	 * 
	 * @param	null|int	$length		(maximum) column value length or `null` to unset previously set value
	 * @return	$this				this column
	 * @throws	\InvalidArgumentException	if given length is invalid
	 */
	public function length($length);
}
