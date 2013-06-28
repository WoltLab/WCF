<?php
namespace wcf\data\label;
use wcf\data\DatabaseObject;
use wcf\system\request\IRouteController;
use wcf\system\WCF;

/**
 * Represents a label.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.label
 * @category	Community Framework
 */
class Label extends DatabaseObject implements IRouteController {
	/**
	 * @see	wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'label';
	
	/**
	 * @see	wcf\data\DatabaseObject::$databaseIndexName
	 */
	protected static $databaseTableIndexName = 'labelID';
	
	/**
	 * Returns the label's textual representation if a label is treated as a
	 * string.
	 * 
	 * @return	string
	 */
	public function __toString() {
		return $this->getTitle();
	}
	
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
		return WCF::getLanguage()->get($this->label);
	}
}
