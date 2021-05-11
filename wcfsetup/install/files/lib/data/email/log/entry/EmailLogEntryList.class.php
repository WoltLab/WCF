<?php

namespace wcf\data\email\log\entry;

use wcf\data\DatabaseObjectList;

/**
 * Represents a list of email log entries.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Data\Emaiil\Log\Entry
 *
 * @method  EmailLogEntry        current()
 * @method  EmailLogEntry[]      getObjects()
 * @method  EmailLogEntry|null       getSingleObject()
 * @method  EmailLogEntry|null       search($objectID)
 * @property    EmailLogEntry[] $objects
 */
class EmailLogEntryList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $className = EmailLogEntry::class;
}
