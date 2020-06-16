<?php
namespace wcf\system\database\table\column;

/**
 * Represents a `decimal` database table column.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Database\Table\Column
 * @since	5.2
 */
class DecimalDatabaseTableColumn extends AbstractDecimalDatabaseTableColumn {
	/**
	 * @inheritDoc
	 */
	protected $type = 'decimal';
	
	/**
	 * @inheritDoc
	 */
	public function decimals($decimals) {
		if ($this->getLength() === null) {
			throw new \BadMethodCallException("Before setting the decimals, the length has to be set.");
		}
		
		return parent::decimals($decimals);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getMaximumDecimals() {
		return 30;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getMaximumLength() {
		return 65;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getMinimumLength() {
		return 1;
	}
}
