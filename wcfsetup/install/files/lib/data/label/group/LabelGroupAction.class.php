<?php
namespace wcf\data\label\group;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes label group-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.label.group
 * @category	Community Framework
 */
class LabelGroupAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\label\group\LabelGroupEditor';
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$permissionsCreate
	 */
	protected $permissionsCreate = array('admin.content.label.canManageLabel');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$permissionsDelete
	 */
	protected $permissionsDelete = array('admin.content.label.canManageLabel');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$permissionsUpdate
	 */
	protected $permissionsUpdate = array('admin.content.label.canManageLabel');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$requireACP
	 */
	protected $requireACP = array('create', 'delete', 'update');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::delete()
	 */
	public function delete() {
		$count = parent::delete();
		
		foreach (ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.label.objectType') as $objectType) {
			$objectType->getProcessor()->save();
		}
		
		return $count;
	}
}
