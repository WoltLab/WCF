<?php

namespace wcf\data\acl\option\category;

use wcf\data\DatabaseObjectList;

/**
 * Represents a list of acl option categories.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  ACLOptionCategory       current()
 * @method  ACLOptionCategory[]     getObjects()
 * @method  ACLOptionCategory|null      getSingleObject()
 * @method  ACLOptionCategory|null      search($objectID)
 * @property    ACLOptionCategory[] $objects
 */
class ACLOptionCategoryList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $className = ACLOptionCategory::class;
}
