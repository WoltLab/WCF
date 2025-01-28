<?php

namespace wcf\data\acp\session\log;

use wcf\data\DatabaseObjectList;

/**
 * Represents a list of session log entries.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  ACPSessionLog       current()
 * @method  ACPSessionLog[]     getObjects()
 * @method  ACPSessionLog|null  getSingleObject()
 * @method  ACPSessionLog|null  search($objectID)
 * @property    ACPSessionLog[] $objects
 */
class ACPSessionLogList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $className = ACPSessionLog::class;
}
