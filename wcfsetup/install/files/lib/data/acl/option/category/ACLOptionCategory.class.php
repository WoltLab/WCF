<?php
namespace wcf\data\acl\option\category;
use wcf\data\DatabaseObject;

/**
 * Represents an acl option category.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Acl\Option\Category
 *
 * @property-read	integer		$categoryID		unique id of the acl option category
 * @property-read	integer		$packageID		id of the package which delivers the acl option category
 * @property-read	integer		$objectTypeID		id of the `com.woltlab.wcf.acl` object type
 * @property-read	string		$categoryName		name and textual identifier of the acl option category
 */
class ACLOptionCategory extends DatabaseObject {}
