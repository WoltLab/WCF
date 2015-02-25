<?php
namespace wcf\data\paid\subscription\user;
use wcf\data\paid\subscription\PaidSubscription;
use wcf\data\user\group\UserGroup;
use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\exception\UserInputException;
use wcf\util\DateUtil;

/**
 * Executes paid subscription user-related actions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.paid.subscription.user
 * @category	Community Framework
 */
class PaidSubscriptionUserAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$permissionsDelete
	 */
	protected $permissionsDelete = array('admin.paidSubscription.canManageSubscription');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$permissionsUpdate
	 */
	protected $permissionsUpdate = array('admin.paidSubscription.canManageSubscription');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$requireACP
	 */
	protected $requireACP = array('create', 'delete', 'update');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::create()
	 */
	public function create() {
		$this->parameters['data']['subscriptionID'] = $this->parameters['subscription']->subscriptionID;
		$this->parameters['data']['userID'] = $this->parameters['user']->userID;
		if (!isset($this->parameters['data']['startDate'])) $this->parameters['data']['startDate'] = TIME_NOW;
		if (!isset($this->parameters['data']['endDate'])) {
			if (!$this->parameters['subscription']->subscriptionLength) {
				$this->parameters['data']['endDate'] = 0;
			}
			else {
				$d = DateUtil::getDateTimeByTimestamp($this->parameters['data']['startDate']);
				$d->add($this->parameters['subscription']->getDateInterval());
				$this->parameters['data']['endDate'] = $d->getTimestamp();
			}
		}
		if (!isset($this->parameters['data']['isActive'])) $this->parameters['data']['isActive'] = 1;
		
		$subscriptionUser = parent::create();
		
		// update group memberships
		$action = new PaidSubscriptionUserAction(array($subscriptionUser), 'addGroupMemberships');
		$action->executeAction();
		
		return $subscriptionUser;
	}
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::validateCreate()
	 */
	public function validateCreate() {
		parent::validateCreate();
		
		if (!isset($this->parameters['subscription']) || !($this->parameters['subscription'] instanceof PaidSubscription)) {
			throw new UserInputException('subscription');
		}
		if (!isset($this->parameters['user']) || !($this->parameters['user'] instanceof User)) {
			throw new UserInputException('user');
		}
	}
	
	/**
	 * Extends an existing subscription.
	 */
	public function extend() {
		if (empty($this->objects)) {
			$this->readObjects();
		}
		
		foreach ($this->objects as $subscriptionUser) {
			$endDate = 0;
			if (!isset($this->parameters['data']['endDate'])) {
				$subscription = $subscriptionUser->getSubscription();
				if ($subscription->subscriptionLength) {
					$d = DateUtil::getDateTimeByTimestamp(TIME_NOW);
					$d->add($subscription->getDateInterval());
					$endDate = $d->getTimestamp();
				}
			}
			else {
				$endDate = $this->parameters['data']['endDate'];
			}
			
			$subscriptionUser->update(array(
				'endDate' => $endDate,
				'isActive' => 1
			));
			
			if (!$subscriptionUser->isActive) {
				// update group memberships
				$action = new PaidSubscriptionUserAction(array($subscriptionUser), 'addGroupMemberships');
				$action->executeAction();
			}
		}
	}
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::delete()
	 */
	public function delete() {
		$this->revoke();
		
		parent::delete();
	}
	
	/**
	 * Revokes an existing subscription.
	 */
	public function revoke() {
		if (empty($this->objects)) {
			$this->readObjects();
		}
		
		foreach ($this->objects as $subscriptionUser) {
			$subscriptionUser->update(array('isActive' => 0));
			
			// update group memberships
			$action = new PaidSubscriptionUserAction(array($subscriptionUser), 'removeGroupMemberships');
			$action->executeAction();
		}
	}
	
	/**
	 * Validates the revoke action.
	 */
	public function validateRevoke() {
		if (empty($this->objects)) {
			$this->readObjects();
		}
		
		foreach ($this->objects as $subscriptionUser) {
			if (!$subscriptionUser->isActive) {
				throw new UserInputException('objectIDs');
			}
		}
	}
	
	/**
	 * Restores an existing subscription.
	 */
	public function restore() {
		if (empty($this->objects)) {
			$this->readObjects();
		}
		
		foreach ($this->objects as $subscriptionUser) {
			$subscriptionUser->update(array('isActive' => 1));
				
			// update group memberships
			$action = new PaidSubscriptionUserAction(array($subscriptionUser), 'addGroupMemberships');
			$action->executeAction();
		}
	}
	
	/**
	 * Validates the restore action.
	 */
	public function validateRestore() {
		if (empty($this->objects)) {
			$this->readObjects();
		}
		
		foreach ($this->objects as $subscriptionUser) {
			if ($subscriptionUser->isActive) {
				throw new UserInputException('objectIDs');
			}
		}
	}
	
	/**
	 * Applies group memberships.
	 */
	public function addGroupMemberships() {
		if (empty($this->objects)) {
			$this->readObjects();
		}
		
		foreach ($this->objects as $subscriptionUser) {
			$groupIDs = array();
			foreach (explode(',', $subscriptionUser->getSubscription()->groupIDs) as $groupID) {
				if (UserGroup::getGroupByID($groupID) !== null) {
					$groupIDs[] = $groupID;
				}
			}
			if (!empty($groupIDs)) {
				$action = new UserAction(array($subscriptionUser->userID), 'addToGroups', array(
					'groups' => $groupIDs,
					'deleteOldGroups' => false,
					'addDefaultGroups' => false
				));
				$action->executeAction();
			}
		}
	}
	
	/**
	 * Removes group memberships.
	 */
	public function removeGroupMemberships() {
		if (empty($this->objects)) {
			$this->readObjects();
		}
		
		foreach ($this->objects as $subscriptionUser) {
			$groupIDs = array();
			foreach (explode(',', $subscriptionUser->getSubscription()->groupIDs) as $groupID) {
				if (UserGroup::getGroupByID($groupID) !== null) {
					$groupIDs[] = $groupID;
				}
			}
			if (!empty($groupIDs)) {
				$action = new UserAction(array($subscriptionUser->userID), 'removeFromGroups', array(
					'groups' => $groupIDs,
				));
				$action->executeAction();
			}
		}
	}
}
