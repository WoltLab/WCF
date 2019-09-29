<?php
namespace wcf\system\database\table\index;

/**
 * Represents a primary index of a database table.
 * 
 * This class just provides a shorter factory method that automatically sets the name and type of
 * the primary index.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Database\Table\Index
 * @since	5.2
 */
class DatabaseTablePrimaryIndex extends DatabaseTableIndex {
	/**
	 * Returns a `PrimaryDatabaseTableIndex` object with `PRIMARY` as name and primary as type.
	 * 
	 * @return	$this
	 */
	public static function create() {
		return parent::create('PRIMARY')
			->type(static::PRIMARY_TYPE);
	}
}
