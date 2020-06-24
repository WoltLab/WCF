<?php
namespace wcf\system\database\table\column;

/**
 * Abstract implementation of a decimal (data) type for database table columns.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Database\Table\Column
 * @since	5.2
 */
abstract class AbstractDecimalDatabaseTableColumn extends AbstractDatabaseTableColumn implements IDecimalsDatabaseTableColumn {
	use TDecimalsDatabaseTableColumn;
	
	/**
	 * @inheritDoc
	 */
	public function getDefaultValue() {
		$defaultValue = parent::getDefaultValue();
		if ($defaultValue === null) {
			return $defaultValue;
		}
		
		return number_format($defaultValue, $this->getDecimals() ?? 0, '.', '');
	}
}
