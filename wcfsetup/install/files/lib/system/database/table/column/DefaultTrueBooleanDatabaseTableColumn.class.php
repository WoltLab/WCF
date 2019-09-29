<?php
namespace wcf\system\database\table\column;

/**
 * Represents a `tinyint` database table column with length `1`, default value `1` and whose values
 * cannot be `null`.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Database\Table\Column
 * @since	5.2
 */
class DefaultTrueBooleanDatabaseTableColumn extends TinyintDatabaseTableColumn {
	/**
	 * @inheritDoc
	 */
	public static function create($name) {
		/** @var TinyintDatabaseTableColumn $column */
		$column = parent::create($name);
		
		return $column
			->length(1)
			->notNull()
			->defaultValue(1);
	}
}
