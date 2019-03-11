<?php
namespace wcf\data\blacklist\status;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of blacklist status.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\Data\Blacklist\Status
 * 
 * @method BlacklistStatus current()
 * @method BlacklistStatus[] getObjects()
 * @method BlacklistStatus|null search($objectID)
 * @property BlacklistStatus[] $objects
 * @since 5.2
 */
class BlacklistStatusList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = BlacklistStatus::class;
}
