<?php

namespace wcf\data\category;

use wcf\data\DatabaseObjectList;

/**
 * Represents a list of categories.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  Category    current()
 * @method  Category[]  getObjects()
 * @method  Category|null   getSingleObject()
 * @method  Category|null   search($objectID)
 * @property    Category[] $objects
 */
class CategoryList extends DatabaseObjectList
{
}
