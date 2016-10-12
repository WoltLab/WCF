<?php
namespace wcf\data\template\group;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes template group-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Template\Group
 * 
 * @method	TemplateGroup		create()
 * @method	TemplateGroupEditor[]	getObjects()
 * @method	TemplateGroupEditor	getSingleObject()
 */
class TemplateGroupAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $className = TemplateGroupEditor::class;
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsCreate = ['admin.template.canManageTemplate'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsDelete = ['admin.template.canManageTemplate'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsUpdate = ['admin.template.canManageTemplate'];
	
	/**
	 * @inheritDoc
	 */
	protected $requireACP = ['create', 'delete', 'update'];
}
