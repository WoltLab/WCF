<?php
namespace wcf\data\user;
use wcf\data\user\group\UserGroup;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\WCF;
use wcf\util\UserRegistrationUtil;

/**
 * Executes user-related actions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.user
 * @subpackage	data.user
 * @category	Community Framework
 */
class ExtendedUserAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$className
	 */
	public $className = 'wcf\data\user\UserEditor';
	
	/**
	 * Validates the enable action.
	 */
	public function validateEnable() {
		WCF::getSession()->checkPermissions(array('admin.user.canEnableUser'));
	}
	
	/**
	 * Validates the disable action.
	 */
	public function validateDisable() {
		$this->validateEnable();
	}
	
	/**
	 * Enables users.
	 */
	public function enable() {
		if (empty($this->objects)) $this->readObjects();
		
		$action = new UserAction($this->objects, 'update', array(
			'data' => array(
				'activationCode' => 0
			),
			'groups' => array(
				UserGroup::USERS
			),
			'removeGroups' => array(
				UserGroup::GUESTS
			)
		));
		$action->executeAction();
		
		// update user rank
		if (MODULE_USER_RANK) {
			$action = new UserProfileAction($this->objects, 'updateUserRank');
			$action->executeAction();
		}
		// update user online marking
		$action = new UserProfileAction($this->objects, 'updateUserOnlineMarking');
		$action->executeAction();
	}
	
	/**
	 * Disables users.
	 */
	public function disable() {
		if (empty($this->objects)) $this->readObjects();
		
		$action = new UserAction($this->objects, 'update', array(
			'data' => array(
				'activationCode' => UserRegistrationUtil::getActivationCode()
			),
			'removeGroups' => array(
				UserGroup::USERS
			),
			'groups' => array(
				UserGroup::GUESTS
			)
		));
		$action->executeAction();
		
		// update user rank
		if (MODULE_USER_RANK) {
			$action = new UserProfileAction($this->objects, 'updateUserRank');
			$action->executeAction();
		}
		// update user online marking
		$action = new UserProfileAction($this->objects, 'updateUserOnlineMarking');
		$action->executeAction();
	}
}
