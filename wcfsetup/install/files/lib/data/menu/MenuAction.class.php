<?php
namespace wcf\data\menu;
use wcf\data\box\BoxAction;
use wcf\data\box\BoxEditor;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\exception\PermissionDeniedException;

/**
 * Executes menu related actions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.menu
 * @category	Community Framework
 * @since	2.2
 */
class MenuAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $className = MenuEditor::class;
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsCreate = ['admin.content.cms.canManageMenu'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsDelete = ['admin.content.cms.canManageMenu'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsUpdate = ['admin.content.cms.canManageMenu'];
	
	/**
	 * @inheritDoc
	 */
	protected $requireACP = ['create', 'delete', 'update'];
	
	/**
	 * @inheritdoc
	 */
	public function create() {
		// create menu
		$menu = parent::create();
		
		// create box
		$boxData = $this->parameters['boxData'];
		$boxData['menuID'] = $menu->menuID;
		$boxData['identifier'] = '';
		$boxAction = new BoxAction([], 'create', ['data' => $boxData, 'pageIDs' => (isset($this->parameters['pageIDs']) ? $this->parameters['pageIDs'] : [])]);
		$returnValues = $boxAction->executeAction();
		
		// set generic box identifier
		$boxEditor = new BoxEditor($returnValues['returnValues']);
		$boxEditor->update([
			'identifier' => 'com.woltlab.wcf.genericMenuBox'.$boxEditor->boxID
		]);
		
		// return new menu
		return $menu;
	}
	
	/**
	 * @inheritDoc
	 */
	public function validateDelete() {
		parent::validateDelete();
		
		foreach ($this->objects as $object) {
			if (!$object->canDelete()) {
				throw new PermissionDeniedException();
			}
		}
	}
}
