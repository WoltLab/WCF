<?php
namespace wcf\data\template\group;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\exception\ValidateActionException;
use wcf\system\WCF;

/**
 * Executes template group-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.template.group
 * @category 	Community Framework
 */
class TemplateGroupAction extends AbstractDatabaseObjectAction {
	/**
	 * @see AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\template\group\TemplateGroupEditor';
	
	/**
	 * @see	AbstractDatabaseObjectAction::$permissionsCreate
	 */
	protected $permissionsCreate = array('admin.template.canAddTemplateGroup');
	
	/**
	 * @see	AbstractDatabaseObjectAction::$permissionsDelete
	 */
	protected $permissionsDelete = array('admin.template.canDeleteTemplateGroup');
	
	/**
	 * @see	AbstractDatabaseObjectAction::$permissionsUpdate
	 */
	protected $permissionsUpdate = array('admin.template.canEditTemplateGroup');
}
