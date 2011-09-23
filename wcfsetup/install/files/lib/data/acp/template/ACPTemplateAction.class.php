<?php
namespace wcf\data\acp\template;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\exception\ValidateActionException;
use wcf\system\WCF;

/**
 * Executes ACP templates-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.acp.template
 * @category 	Community Framework
 */
class ACPTemplateAction extends AbstractDatabaseObjectAction {
	/**
	 * @see wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\acp\template\ACPTemplateEditor';
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$createPermissions
	 */
	protected $createPermissions = array('admin.template.canAddTemplate');
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$deletePermissions
	 */
	protected $deletePermissions = array('admin.template.canDeleteTemplate');
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$updatePermissions
	 */
	protected $updatePermissions = array('admin.template.canUpdateTemplate');
}
