<?php

namespace wcf\data\acl\option\category;

use wcf\data\DatabaseObject;

/**
 * Represents an acl option category.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int $categoryID     unique id of the acl option category
 * @property-read   int $packageID      id of the package which delivers the acl option category
 * @property-read   int $objectTypeID       id of the `com.woltlab.wcf.acl` object type
 * @property-read   string $categoryName       name and textual identifier of the acl option category
 */
class ACLOptionCategory extends DatabaseObject
{
}
