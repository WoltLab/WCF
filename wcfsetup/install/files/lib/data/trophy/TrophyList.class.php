<?php

namespace wcf\data\trophy;

use wcf\data\DatabaseObjectList;
use wcf\system\cache\runtime\FileRuntimeCache;

/**
 * Represents a trophy list.
 *
 * @author  Joshua Ruesweg, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends DatabaseObjectList<Trophy>
 */
class TrophyList extends DatabaseObjectList
{
    #[\Override]
    public function readObjects()
    {
        parent::readObjects();

        $iconFileIDs = [];
        foreach ($this->objects as $object) {
            if ($object->iconFileID !== null) {
                $iconFileIDs[] = $object->iconFileID;
            }
        }
        FileRuntimeCache::getInstance()->cacheObjectIDs($iconFileIDs);
    }
}
