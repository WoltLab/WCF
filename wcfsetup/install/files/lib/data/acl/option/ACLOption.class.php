<?php
namespace wcf\data\acl\option;
use wcf\data\DatabaseObject;

/**
 * Represents an acl option.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.acl.option
 * @category	Community Framework
 *
 * @property-read	integer		$optionID
 * @property-read	integer		$packageID
 * @property-read	integer		$objectTypeID
 * @property-read	string		$optionName
 * @property-read	string		$categoryName
 */
class ACLOption extends DatabaseObject {
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'acl_option';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'optionID';
	
	/**
	 * Returns a list of options by object type id.
	 * 
	 * @param	integer		$objectTypeID
	 * @return	\wcf\data\acl\option\ACLOptionList
	 */
	public static function getOptions($objectTypeID) {
		$optionList = new ACLOptionList();
		$optionList->getConditionBuilder()->add("acl_option.objectTypeID = ?", [$objectTypeID]);
		$optionList->readObjects();
		
		return $optionList;
	}
}
