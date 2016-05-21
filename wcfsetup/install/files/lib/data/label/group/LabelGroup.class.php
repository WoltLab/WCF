<?php
namespace wcf\data\label\group;
use wcf\data\DatabaseObject;
use wcf\system\request\IRouteController;
use wcf\system\WCF;

/**
 * Represents a label group.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.label.group
 * @category	Community Framework
 *
 * @property-read	integer		$groupID
 * @property-read	string		$groupName
 * @property-read	string		$groupDescription
 * @property-read	integer		$forceSelection
 * @property-read	integer		$showOrder
 */
class LabelGroup extends DatabaseObject implements IRouteController {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'label_group';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'groupID';
	
	/**
	 * @inheritDoc
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
