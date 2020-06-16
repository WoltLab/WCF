<?php
namespace wcf\system\database\table\column;

/**
 * Provides default implementation of the methods of `ILengthDatabaseTableColumn`.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Database\Table\Column
 * @since	5.2
 */
trait TLengthDatabaseTableColumn {
	/**
	 * (maximum) length of the column's values
	 * @var	null|int
	 */
	protected $length;
	
	/**
	 * Returns the maxium length value supported by this column or `null` if there is no such
	 * maximum.
	 *
	 * @return	null|int
	 */
	public function getMaximumLength() {
		return null;
	}
	
	/**
	 * Returns the minimum length value supported by this column or `null` if there is no such
	 * minimum.
	 * 
	 * @return	null|int
	 */
	public function getMinimumLength() {
		return null;
	}
	
	/**
	 * Returns the (maximum) length of the column's values or `null` if no length has been set.
	 * 
	 * @return	null|int
	 */
	public function getLength() {
		return $this->length;
	}
	
	/**
	 * Sets the (maximum) length of the column's values.
	 * 
	 * @param	null|int	$length		(maximum) column value length or `null` to unset previously set value
	 * @return	$this				this column
	 * @throws	\InvalidArgumentException	if given length is invalid
	 */
	public function length($length) {
		if ($length !== null) {
			$this->validateLength($length);
		}
		
		$this->length = $length;
		
		return $this;
	}
	
	/**
	 * Validates the given length.
	 * 
	 * @param	int	$length
	 * @throws	\InvalidArgumentException	if given length is invalid
	 */
	protected function validateLength($length) {
		if ($this->getMinimumLength() !== null && $length < $this->getMinimumLength()) {
			throw new \InvalidArgumentException("Given length is smaller than the minimum length '{$this->getMinimumLength()}'.");
		}
		if ($this->getMaximumLength() !== null && $length > $this->getMaximumLength()) {
			throw new \InvalidArgumentException("Given length is greater than the maximum length '{$this->getMaximumLength()}'.");
		}
	}
}
