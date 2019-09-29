<?php
namespace wcf\system\database\table\column;

/**
 * Represents a `varchar` database table column with length `191` and whose values cannot be null.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Database\Table\Column
 * @since	5.2
 */
class NotNullVarchar191DatabaseTableColumn extends VarcharDatabaseTableColumn {
	/**
	 * @inheritDoc
	 */
	public static function create($name) {
		return parent::create($name)
			->notNull()
			->length(191);
	}
}
