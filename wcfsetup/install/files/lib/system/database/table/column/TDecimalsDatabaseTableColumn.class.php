<?php
namespace wcf\system\database\table\column;

/**
 * Provides default implementation of the methods of `IDecimalsDatabaseTableColumn`.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Database\Table\Column
 * @since	5.2
 */
trait TDecimalsDatabaseTableColumn {
	use TLengthDatabaseTableColumn;
	
	/**
	 * number of decimals the database table column supports
	 * @var	null|int
	 */
	protected $decimals;
	
	/**
	 * Sets the number of decimals the database table column supports or unsets the previously
	 * set value if `null` is passed and returns this column.
	 *
	 * @param	null|int	$decimals
	 * @return	$this
	 */
	public function decimals($decimals) {
		if ($this->getMaximumDecimals() !== null && $decimals > $this->getMaximumDecimals()) {
			throw new \InvalidArgumentException("Given number of decimals is greater than the maximum number '{$this->getMaximumDecimals()}'.");
		}
		
		$this->decimals = $decimals;
		
		return $this;
	}

	/**
	 * Returns the number of decimals the database table column supports or `null` if the number
	 * of decimals has not be specified.
	 *
	 * @return	null|int
	 */
	public function getDecimals() {
		return $this->decimals;
	}
	
	/**
	 * Returns the maxium number of decimals supported by this column or `null` if there is no such
	 * maximum.
	 *
	 * @return	null|int
	 */
	public function getMaximumDecimals() {
		return null;
	}
}
