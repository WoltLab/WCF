<?php
namespace wcf\system\edit;
use wcf\data\edit\history\entry\EditHistoryEntry;
use wcf\data\IDatabaseObjectProcessor;
use wcf\data\IUserContent;

/**
 * Represents an object that saves it's edit history.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Edit
 */
interface IHistorySavingObject extends IDatabaseObjectProcessor, IUserContent {
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
	 * Reverts the object's text to the given EditHistoryEntry.
	 * 
	 * @param	EditHistoryEntry	$edit
	 */
	public function revertVersion(EditHistoryEntry $edit);
	
	/**
	 * Sets the page location data.
	 */
	public function setLocation();
}
