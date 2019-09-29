<?php
namespace wcf\system\database\table\column;

/**
 * Provides default implementation of the methods of `IEnumDatabaseTableColumn`.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Database\Table\Column
 * @since	5.2
 */
trait TEnumDatabaseTableColumn {
	/**
	 * predetermined set of valid values for the database table column
	 * @var	array
	 */
	protected $enumValues = [];
	
	/**
	 * Sets the predetermined set of valid values for the database table column and returns this
	 * column.
	 *
	 * @param	array		$values
	 * @return	$this
	 */
	public function enumValues(array $values) {
		$this->enumValues = $values;
		
		return $this;
	}
	
	/**
	 * Returns the predetermined set of valid values for the database table column.
	 *
	 * @return	array
	 */
	public function getEnumValues() {
		return $this->enumValues;
	}
}
