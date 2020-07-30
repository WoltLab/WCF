<?php
namespace wcf\page;
use wcf\data\paid\subscription\user\PaidSubscriptionUserList;
use wcf\system\cache\builder\PaidSubscriptionCacheBuilder;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\menu\user\UserMenu;
use wcf\system\WCF;

/**
 * Shows a list of the available paid subscriptions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Page
 */
class PaidSubscriptionListPage extends AbstractPage {
	/**
	 * @inheritDoc
	 */
	public $loginRequired = true;
	
	/**
	 * @inheritDoc
	 */
	public $neededModules = ['MODULE_PAID_SUBSCRIPTION'];
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['user.profile.payment.canBuyPaidSubscription'];
	
	/**
	 * list of available paid subscriptions
	 * @var	array
	 */
	public $subscriptions = [];
	
	/**
	 * list of user subscriptions
	 * @var	PaidSubscriptionUserList
	 */
	public $userSubscriptionList = [];
	
	/**
	 * @inheritDoc
	 */
	public function checkPermissions() {
		parent::checkPermissions();
		
		if (WCF::getUser()->pendingActivation()) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		// get available subscriptions
		$this->subscriptions = PaidSubscriptionCacheBuilder::getInstance()->getData();
		
		// get user subscriptions
		$this->userSubscriptionList = new PaidSubscriptionUserList();
		$this->userSubscriptionList->getConditionBuilder()->add('userID = ?', [WCF::getUser()->userID]);
		$this->userSubscriptionList->getConditionBuilder()->add('isActive = ?', [1]);
		$this->userSubscriptionList->readObjects();
		
		foreach ($this->userSubscriptionList as $userSubscription) {
			if (isset($this->subscriptions[$userSubscription->subscriptionID])) {
				$userSubscription->setSubscription($this->subscriptions[$userSubscription->subscriptionID]);
				unset($this->subscriptions[$userSubscription->subscriptionID]);
			}
		}
		foreach ($this->userSubscriptionList as $userSubscription) {
			if ($userSubscription->getSubscription()->excludedSubscriptionIDs) {
				foreach (explode(',', $userSubscription->getSubscription()->excludedSubscriptionIDs) as $subscriptionID) {
					if (isset($this->subscriptions[$subscriptionID])) unset($this->subscriptions[$subscriptionID]);
				}
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'subscriptions' => $this->subscriptions,
			'userSubscriptions' => $this->userSubscriptionList
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function show() {
		// set active tab
		UserMenu::getInstance()->setActiveMenuItem('wcf.user.menu.settings.paidSubscription');
		
		parent::show();
	}
}
