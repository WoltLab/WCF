<?php
namespace wcf\data\blacklist\entry;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of blacklist entries.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\Data\Blacklist\Entry
 * 
 * @method BlacklistEntry current()
 * @method BlacklistEntry[] getObjects()
 * @method BlacklistEntry|null search($objectID)
 * @property BlacklistEntry[] $objects
 * @since 5.2
 */
class BlacklistEntryList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = BlacklistEntry::class;
}
