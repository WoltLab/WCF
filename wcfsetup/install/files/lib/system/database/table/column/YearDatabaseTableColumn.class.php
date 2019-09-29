<?php
namespace wcf\system\database\table\column;

/**
 * Represents a `year` database table column.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Database\Table\Column
 * @since	5.2
 */
class YearDatabaseTableColumn extends AbstractDatabaseTableColumn implements ILengthDatabaseTableColumn {
	use TLengthDatabaseTableColumn;
	
	/**
	 * @inheritDoc
	 */
	protected $type = 'year';
	
	/**
	 * @inheritDoc
	 */
	protected function validateLength($length) {
		if ($length !== 2 && $length !== 4) {
			throw new \InvalidArgumentException("Only '2' and '4' are valid lengths for year columns");
		}
	}
}
