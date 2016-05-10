<?php
namespace wcf\data\moderation\queue;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of moderation queue entries.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.moderation.queue
 * @category	Community Framework
 *
 * @method	ModerationQueue		current()
 * @method	ModerationQueue[]	getObjects()
 * @method	ModerationQueue|null	search($objectID)
 * @property	ModerationQueue[]	$objects
 */
class ModerationQueueList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = ModerationQueue::class;
}
