<?php
namespace wcf\system\database\table\column;

/**
 * Provides default implementation of the methods of `IUnsignedDatabaseTableColumn`.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Database\Table\Column
 * @since	5.2
 */
trait TUnsignedDatabaseTableColumn {
	/**
	 * `true` if the values of the database table column are unsigned
	 * @var	bool
	 */
	protected $unsigned = false;
	
	/**
	 * Returns `true` if the values of the database table column are unsigned.
	 * 
	 * @return	bool
	 */
	public function isUnsigned() {
		return $this->unsigned;
	}
	
	/**
	 * Sets if the values of the database table column are unsigned and returns this column.
	 * 
	 * @param	bool	$unsigned
	 * @return	$this
	 */
	public function unsigned($unsigned = true) {
		$this->unsigned = $unsigned;
		
		return $this;
	}
}
