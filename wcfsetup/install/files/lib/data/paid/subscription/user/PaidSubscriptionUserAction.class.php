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
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Paid\Subscription\User
 * 
 * @method	PaidSubscriptionUserEditor[]	getObjects()
 * @method	PaidSubscriptionUserEditor	getSingleObject()
 */
class PaidSubscriptionUserAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $permissionsDelete = ['admin.paidSubscription.canManageSubscription'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsUpdate = ['admin.paidSubscription.canManageSubscription'];
	
	/**
	 * @inheritDoc
	 */
	protected $requireACP = ['create', 'delete', 'update'];
	
	/**
	 * @inheritDoc
	 * @return	PaidSubscriptionUser
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
				/** @noinspection PhpUndefinedMethodInspection */
				$d->add($this->parameters['subscription']->getDateInterval());
				$this->parameters['data']['endDate'] = $d->getTimestamp();
			}
		}
		if (!isset($this->parameters['data']['isActive'])) $this->parameters['data']['isActive'] = 1;
		
		/** @var PaidSubscriptionUser $subscriptionUser */
		$subscriptionUser = parent::create();
		
		// update group memberships
		$action = new PaidSubscriptionUserAction([$subscriptionUser], 'addGroupMemberships');
		$action->executeAction();
		
		return $subscriptionUser;
	}
	
	/**
	 * @inheritDoc
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
		
		foreach ($this->getObjects() as $subscriptionUser) {
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
			
			$subscriptionUser->update([
				'endDate' => $endDate,
				'isActive' => 1
			]);
			
			if (!$subscriptionUser->isActive) {
				// update group memberships
				$action = new PaidSubscriptionUserAction([$subscriptionUser], 'addGroupMemberships');
				$action->executeAction();
			}
		}
	}
	
	/**
	 * @inheritDoc
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
		
		foreach ($this->getObjects() as $subscriptionUser) {
			$subscriptionUser->update(['isActive' => 0]);
			
			// update group memberships
			$action = new PaidSubscriptionUserAction([$subscriptionUser], 'removeGroupMemberships');
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
		
		foreach ($this->getObjects() as $subscriptionUser) {
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
		
		foreach ($this->getObjects() as $subscriptionUser) {
			$subscriptionUser->update(['isActive' => 1]);
			
			// update group memberships
			$action = new PaidSubscriptionUserAction([$subscriptionUser], 'addGroupMemberships');
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
		
		foreach ($this->getObjects() as $subscriptionUser) {
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
		
		foreach ($this->getObjects() as $subscriptionUser) {
			$groupIDs = [];
			foreach (explode(',', $subscriptionUser->getSubscription()->groupIDs) as $groupID) {
				if (UserGroup::getGroupByID($groupID) !== null) {
					$groupIDs[] = $groupID;
				}
			}
			if (!empty($groupIDs)) {
				$action = new UserAction([$subscriptionUser->userID], 'addToGroups', [
					'groups' => $groupIDs,
					'deleteOldGroups' => false,
					'addDefaultGroups' => false
				]);
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
		
		foreach ($this->getObjects() as $subscriptionUser) {
			$groupIDs = [];
			foreach (explode(',', $subscriptionUser->getSubscription()->groupIDs) as $groupID) {
				if (UserGroup::getGroupByID($groupID) !== null) {
					$groupIDs[] = $groupID;
				}
			}
			if (!empty($groupIDs)) {
				$action = new UserAction([$subscriptionUser->userID], 'removeFromGroups', [
					'groups' => $groupIDs
				]);
				$action->executeAction();
			}
		}
	}
}
