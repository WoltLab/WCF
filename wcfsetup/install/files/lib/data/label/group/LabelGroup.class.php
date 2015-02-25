<?php
namespace wcf\data\label\group;
use wcf\data\DatabaseObject;
use wcf\system\request\IRouteController;
use wcf\system\WCF;

/**
 * Represents a label group.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.label.group
 * @category	Community Framework
 */
class LabelGroup extends DatabaseObject implements IRouteController {
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'label_group';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseIndexName
	 */
	protected static $databaseTableIndexName = 'groupID';
	
	/**
	 * @see	\wcf\data\ITitledObject::getTitle()
	 */
	public function getTitle() {
		return WCF::getLanguage()->get($this->groupName);
	}
	
	/**
	 * Returns label group title.
	 * 
	 * @return	string
	 */
	public function __toString() {
		return $this->getTitle();
	}
	
	/**
	 * Callback for uasort() to sort label groups by show order and (if equal) group id.
	 * 
	 * @param	\wcf\data\DatabaseObject	$groupA
	 * @param	\wcf\data\DatabaseObject	$groupB
	 * @return	integer
	 */
	public static function sortLabelGroups(DatabaseObject $groupA, DatabaseObject $groupB) {
		if ($groupA->showOrder == $groupB->showOrder) {
			return ($groupA->groupID > $groupB->groupID) ? 1 : -1;
		}
		
		return ($groupA->showOrder > $groupB->showOrder) ? 1 : -1;
	}
}
