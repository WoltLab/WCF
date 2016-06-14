<?php
namespace wcf\data\option\category;
use wcf\data\DatabaseObject;
use wcf\data\TDatabaseObjectOptions;
use wcf\data\TDatabaseObjectPermissions;

/**
 * Represents an option category.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Option\Category
 *
 * @property-read	integer		$categoryID
 * @property-read	integer		$packageID
 * @property-read	string		$categoryName
 * @property-read	string		$parentCategoryName
 * @property-read	integer		$showOrder
 * @property-read	string		$permissions
 * @property-read	string		$options
 */
class OptionCategory extends DatabaseObject {
	use TDatabaseObjectOptions;
	use TDatabaseObjectPermissions;
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'option_category';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'categoryID';
}
