<?php
namespace wcf\system\edit;
use wcf\data\edit\history\entry\EditHistoryEntry;
use wcf\data\IDatabaseObjectProcessor;
use wcf\data\IUserContent;

/**
 * Represents an object that saves it's edit history.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.edit
 * @category	Community Framework
 */
interface IHistorySavingObject extends IDatabaseObjectProcessor, IUserContent {
	/**
	 * Reverts the object's text to the given EditHistoryEntry.
	 * 
	 * @param	\wcf\data\edit\history\entry\EditHistoryEntry
	 */
	public function revertVersion(EditHistoryEntry $edit);
	
	/**
	 * Returns the object's current edit reason.
	 * 
	 * @return	string
	 */
	public function getEditReason();
	
	/**
	 * Returns the object's current message text.
	 * 
	 * @return	string
	 */
	public function getMessage();
	
	/**
	 * Adds the object's breadcrumbs.
	 */
	public function addBreadcrumbs();
}
