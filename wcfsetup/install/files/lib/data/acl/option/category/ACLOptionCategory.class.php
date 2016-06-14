<?php
namespace wcf\data\acl\option\category;
use wcf\data\DatabaseObject;

/**
 * Represents an acl option category.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Acl\Option\Category
 *
 * @property-read	integer		$categoryID
 * @property-read	integer		$packageID
 * @property-read	integer		$objectTypeID
 * @property-read	string		$categoryName
 */
class ACLOptionCategory extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'acl_option_category';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'categoryID';
}
