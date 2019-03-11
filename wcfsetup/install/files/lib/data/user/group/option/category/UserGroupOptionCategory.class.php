<?php
namespace wcf\data\user\group\option\category;
use wcf\data\DatabaseObject;
use wcf\data\TDatabaseObjectOptions;
use wcf\data\TDatabaseObjectPermissions;

/**
 * Represents a user group options category.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User\Group\Option\Category
 *
 * @property-read	integer		$categoryID		unique id of the user group options category
 * @property-read	integer		$packageID		id of the package which delivers the user group options category
 * @property-read	string		$categoryName		name and textual identifier of the user group option category
 * @property-read	string		$parentCategoryName	name of the user group option category's parent category or empty if it has no parent category
 * @property-read	integer		$showOrder		position of the user group options category in relation to its siblings
 * @property-read	string		$permissions		comma separated list of user group permissions of which the active user needs to have at least one to see the user group options category
 * @property-read	string		$options		comma separated list of options of which at least one needs to be enabled for the user group options category to be shown
 */
class UserGroupOptionCategory extends DatabaseObject {
	use TDatabaseObjectOptions;
	use TDatabaseObjectPermissions;
}
