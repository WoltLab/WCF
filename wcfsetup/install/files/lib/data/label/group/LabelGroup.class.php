<?php
namespace wcf\data\label\group;
use wcf\data\DatabaseObject;
use wcf\system\request\IRouteController;
use wcf\system\WCF;

/**
 * Represents a label group.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.label.group
 * @category	Community Framework
 */
class LabelGroup extends DatabaseObject implements IRouteController {
	/**
	 * @see	wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'label_group';
	
	/**
	 * @see	wcf\data\DatabaseObject::$databaseIndexName
	 */
	protected static $databaseTableIndexName = 'groupID';
	
	/**
	 * Returns true, if label is editable by current user.
	 * 
	 * @return	boolean
	 */
	public function isEditable() {
		if (WCF::getSession()->getPermission('admin.content.label.canManageLabel')) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Returns true, if label is deletable by current user.
	 * 
	 * @return	boolean
	 */
	public function isDeletable() {
		if (WCF::getSession()->getPermission('admin.content.label.canManageLabel')) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * @see	wcf\data\ITitledObject::getTitle()
	 */
	public function getTitle() {
		return $this->groupName;
	}
}
