<?php
namespace wcf\data\edit\history\entry;
use wcf\data\DatabaseObject;

/**
 * Represents an edit history entry
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.edit.history.entry
 * @category	Community Framework
 */
class EditHistoryEntry extends DatabaseObject {
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'edit_history_entry';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'entryID';
	
	/**
	 * Returns the message text of the history entry.
	 * 
	 * @return	string
	 */
	public function getMessage() {
		return $this->message;
	}
}
