<?php
namespace wcf\data\user\group\option\category;
use wcf\data\DatabaseObject;
use wcf\data\TDatabaseObjectOptions;
use wcf\data\TDatabaseObjectPermissions;

/**
 * Represents a user group options category.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.group.option.category
 * @category	Community Framework
 *
 * @property-read	integer		$categoryID
 * @property-read	integer		$packageID
 * @property-read	string		$categoryName
 * @property-read	string		$parentCategoryName
 * @property-read	integer		$showOrder
 * @property-read	string		$permissions
 * @property-read	string		$options
 */
class UserGroupOptionCategory extends DatabaseObject {
	use TDatabaseObjectOptions;
	use TDatabaseObjectPermissions;
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'user_group_option_category';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'categoryID';
}
