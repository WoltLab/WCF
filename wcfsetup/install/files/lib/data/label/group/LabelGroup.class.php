<?php
namespace wcf\data\label\group;
use wcf\data\DatabaseObject;
use wcf\system\request\IRouteController;

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
	 * @see	wcf\data\ITitledObject::getTitle()
	 */
	public function getTitle() {
		return $this->groupName;
	}
}
