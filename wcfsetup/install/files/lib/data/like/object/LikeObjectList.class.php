<?php

namespace wcf\data\like\object;

use wcf\data\DatabaseObjectList;

/**
 * Represents a list of like objects.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends DatabaseObjectList<LikeObject>
 */
class LikeObjectList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $className = LikeObject::class;
}
