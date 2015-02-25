<?php
namespace wcf\data\moderation\queue;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of moderation queue entries.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.moderation.queue
 * @category	Community Framework
 */
class ModerationQueueList extends DatabaseObjectList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\moderation\queue\ModerationQueue';
}
