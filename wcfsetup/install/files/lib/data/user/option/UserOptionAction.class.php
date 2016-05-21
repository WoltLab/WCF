<?php
namespace wcf\data\user\option;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IToggleAction;
use wcf\system\exception\PermissionDeniedException;

/**
 * Executes user option-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.option
 * @category	Community Framework
 */
class UserOptionAction extends AbstractDatabaseObjectAction implements IToggleAction {
	/**
	 * @inheritDoc
	 */
	protected $className = 'wcf\data\user\option\UserOptionEditor';
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsCreate = ['admin.user.canManageUserOption'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsDelete = ['admin.user.canManageUserOption'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsUpdate = ['admin.user.canManageUserOption'];
	
	/**
	 * @inheritDoc
	 */
	protected $requireACP = ['create', 'delete', 'toggle', 'update'];
	
	/**
	 * @inheritDoc
	 */
	public function validateDelete() {
		parent::validateDelete();
		
		foreach ($this->objects as $userOption) {
			if (!$userOption->canDelete()) {
				throw new PermissionDeniedException();
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function toggle() {
		foreach ($this->objects as $optionEditor) {
			$optionEditor->update([
				'isDisabled' => 1 - $optionEditor->isDisabled
			]);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function validateToggle() {
		$this->validateUpdate();
	}
}
