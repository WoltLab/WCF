<?php
namespace wcf\data\edit\history\entry;
use wcf\data\AbstractDatabaseObjectAction;

/**
* Executes edit history entry-related actions.
* 
* @author	Tim Duesterhus
* @copyright	2001-2014 WoltLab GmbH
* @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
* @package	com.woltlab.wcf
* @subpackage	data.edit.history.entry
* @category	Community Framework
*/
class EditHistoryEntryAction extends AbstractDatabaseObjectAction {
	/**
	* @see	\wcf\data\AbstractDatabaseObjectAction::$className
	*/
	protected $className = 'wcf\data\edit\history\entry\EditHistoryEntryEditor';
}
