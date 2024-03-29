<?php

namespace wcf\data\condition;

use wcf\data\DatabaseObjectList;

/**
 * Represents a list of conditions.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  Condition       current()
 * @method  Condition[]     getObjects()
 * @method  Condition|null      getSingleObject()
 * @method  Condition|null      search($objectID)
 * @property    Condition[] $objects
 */
class ConditionList extends DatabaseObjectList
{
}
