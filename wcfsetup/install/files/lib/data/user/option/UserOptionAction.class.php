<?php
namespace wcf\data\user\option;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IToggleAction;

/**
 * Executes user option-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.option
 * @category	Community Framework
 */
class UserOptionAction extends AbstractDatabaseObjectAction implements IToggleAction {
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\user\option\UserOptionEditor';
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$permissionsCreate
	 */
	protected $permissionsCreate = array('admin.user.canManageUserOption');
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$permissionsDelete
	 */
	protected $permissionsDelete = array('admin.user.canManageUserOption');
	
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$permissionsUpdate
	 */
	protected $permissionsUpdate = array('admin.user.canManageUserOption');
	
	/**
	 * @see	wcf\data\IToggleAction::toggle()
	 */
	public function toggle() {
		foreach ($this->objects as $optionEditor) {
			$optionEditor->update(array(
				'isDisabled' => 1 - $optionEditor->isDisabled
			));
		}
	}
	
	/**
	 * @see	wcf\data\IToggleAction::validateToggle()
	 */
	public function validateToggle() {
		$this->validateUpdate();
	}
}
