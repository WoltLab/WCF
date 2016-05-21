<?php
namespace wcf\data\acl\option\category;
use wcf\data\DatabaseObject;

/**
 * Represents an acl option category.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.acl.option.category
 * @category	Community Framework
 *
 * @property-read	integer		$categoryID
 * @property-read	integer		$packageID
 * @property-read	integer		$objectTypeID
 * @property-read	string		$categoryName
 */
class ACLOptionCategory extends DatabaseObject {
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'acl_option_category';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'categoryID';
}
