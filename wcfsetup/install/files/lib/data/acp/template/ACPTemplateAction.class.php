<?php
namespace wcf\data\acp\template;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes ACP templates-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Acp\Template
 * 
 * @method	ACPTemplate		create()
 * @method	ACPTemplateEditor[]	getObjects()
 * @method	ACPTemplateEditor	getSingleObject()
 */
class ACPTemplateAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $className = ACPTemplateEditor::class;
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsCreate = ['admin.template.canAddTemplate'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsDelete = ['admin.template.canDeleteTemplate'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsUpdate = ['admin.template.canUpdateTemplate'];
	
	/**
	 * @inheritDoc
	 */
	protected $requireACP = ['create', 'delete', 'update'];
}
