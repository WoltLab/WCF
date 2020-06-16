<?php
namespace wcf\system\database\table\column;

/**
 * Provides default implementation of the methods of `IAutoIncrementDatabaseTableColumn`.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Database\Table\Column
 * @since	5.2
 */
trait TAutoIncrementDatabaseTableColumn {
	/**
	 * is `true` if the values of the database table column are auto-increment
	 * @var	bool
	 */
	protected $autoIncrement = false;
	
	/**
	 * Sets if the values of the database table column are auto-increment and returns this column.
	 *
	 * @param	bool	$autoIncrement
	 * @return	$this
	 */
	public function autoIncrement($autoIncrement = true) {
		$this->autoIncrement = $autoIncrement;
		
		return $this;
	}
	
	/**
	 * Returns `true` if the values of the database table column are auto-increment.
	 *
	 * @return	bool
	 */
	public function isAutoIncremented() {
		return $this->autoIncrement;
	}
}
