<?php
namespace wcf\data\edit\history\entry;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of edit history entries.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.edit.history.entry
 * @category	Community Framework
 */
class EditHistoryEntryList extends DatabaseObjectList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\edit\history\entry\EditHistoryEntry';
}
