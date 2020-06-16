<?php
namespace wcf\data\acl\option;
use wcf\data\DatabaseObject;

/**
 * Represents an acl option.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Acl\Option
 *
 * @property-read	integer		$optionID		unique id of the acl option
 * @property-read	integer		$packageID		id of the package which delivers the acl option
 * @property-read	integer		$objectTypeID		id of the `com.woltlab.wcf.acl` object type
 * @property-read	string		$optionName		name and textual identifier of the acl option
 * @property-read	string		$categoryName		name of the acl option category the option belongs to
 */
class ACLOption extends DatabaseObject {
	/**
	 * Returns a list of options by object type id.
	 * 
	 * @param	integer		$objectTypeID
	 * @return	ACLOptionList
	 */
	public static function getOptions($objectTypeID) {
		$optionList = new ACLOptionList();
		$optionList->getConditionBuilder()->add("acl_option.objectTypeID = ?", [$objectTypeID]);
		$optionList->readObjects();
		
		return $optionList;
	}
}
